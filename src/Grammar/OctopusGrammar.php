<?php

namespace Alberteddu\Octopus\Grammar;

use Alberteddu\Octopus\Parser\DTO\BlueprintArgument;
use Alberteddu\Octopus\Parser\DTO\BlueprintVariable;
use Alberteddu\Octopus\Parser\ParserCommand\DefineCommand;
use Alberteddu\Octopus\Parser\ParserCommand\NewCommand;
use Alberteddu\Octopus\Parser\ParserCommand\ToCommand;
use Ferno\Loco\ConcParser;
use Ferno\Loco\EmptyParser;
use Ferno\Loco\Grammar;
use Ferno\Loco\GreedyStarParser;
use Ferno\Loco\LazyAltParser;
use Ferno\Loco\RegexParser;
use Ferno\Loco\StringParser;

class OctopusGrammar
{
    public static function getGrammar()
    {
        $implode = function () {
            return implode('', func_get_args());
        };

        $decode = function ($val) {
            return json_decode($val, true);
        };

        $decodeImplode = function () use ($decode, $implode) {
            return $decode($implode(...func_get_args()));
        };

        return new Grammar(
            '<octopus>',
            array(
                '<octopus>' => new ConcParser(
                    array('<whitespace>', '<commands>'),
                    function ($whitespace, $commands) {
                        return $commands;
                    }
                ),
                '<commands>' => new GreedyStarParser(
                    '<commandorblankline>',
                    function () {
                        $commands = array();
                        foreach (func_get_args() as $commandorblankline) {
                            if ($commandorblankline === null) {
                                continue;
                            }
                            $commands[] = $commandorblankline;
                        }

                        return $commands;
                    }
                ),
                '<commandorblankline>' => new LazyAltParser(
                    array('<command>', '<blankline>')
                ),
                '<blankline>' => new ConcParser(
                    array(
                        new RegexParser("#^\r?\n#"),
                        '<whitespace>'
                    ),
                    function () {
                        return null;
                    }
                ),

                '<command>' => new LazyAltParser(
                    [
                        '<tocommand>',
                        '<definecommand>',
                        '<newcommand>',
                    ]
                ),

                '<tocommand>' => new ConcParser(
                    [
                        new StringParser('to'),
                        '<whitespace>',
                        '<string>',
                        '<whitespace>'
                    ],
                    function ($to, $w1, $destination, $w2) {
                        return new ToCommand($destination);
                    }
                ),

                '<definecommand>' => new ConcParser(
                    [
                        new StringParser('def'),
                        '<whitespace>',
                        '<variableidentifier>',
                        '<whitespace>',
                        new StringParser('vars'),
                        '<whitespace>',
                        new StringParser('{'),
                        '<whitespace>',
                        '<variables>',
                        '<whitespace>',
                        new StringParser('}')
                    ],
                    function ($to, $w1, $name, $w2, $vars, $w3, $paren, $w4, $variables) {
                        return new DefineCommand($name, $variables);
                    }
                ),

                '<newcommand>' => new ConcParser(
                    [
                        new StringParser('new'),
                        '<whitespace>',
                        '<variableidentifier>',
                        '<whitespace>',
                        new StringParser('{'),
                        '<whitespace>',
                        '<arguments>',
                        '<whitespace>',
                        new StringParser('}')
                    ],
                    function ($new, $w1, $name, $w2, $paren, $w3, $arguments) {
                        return new NewCommand($name, $arguments);
                    }
                ),

                '<variables>' => new GreedyStarParser(
                    '<variableorblankline>',
                    function () {
                        $variables = array();
                        foreach (func_get_args() as $variableorblankline) {
                            if ($variableorblankline === null) {
                                continue;
                            }
                            $variables[] = $variableorblankline;
                        }

                        return $variables;
                    }
                ),
                '<arguments>' => new GreedyStarParser(
                    '<argumentorblankline>',
                    function () {
                        $arguments = array();
                        foreach (func_get_args() as $argumentorblankline) {
                            if ($argumentorblankline === null) {
                                continue;
                            }
                            $arguments[] = $argumentorblankline;
                        }

                        return $arguments;
                    }
                ),

                '<variableorblankline>' => new LazyAltParser(
                    array('<variable>', '<blankline>')
                ),

                '<argumentorblankline>' => new LazyAltParser(
                    array('<argument>', '<blankline>')
                ),

                '<variable>' => new ConcParser(
                    [
                        '<whitespacenonl>',
                        new LazyAltParser([
                            new StringParser('boolean'),
                            new StringParser('string'),
                            new StringParser('integer'),
                            new StringParser('float'),
                            new StringParser('array'),
                            new StringParser('object'),
                        ]),
                        new LazyAltParser(
                            [
                                new StringParser('!', function () {
                                    return true;
                                }),
                                new EmptyParser(function () {
                                    return false;
                                })
                            ]
                        ),
                        '<whitespacenonl>',
                        '<variabledefinition>',
                        '<whitespacenonl>',
                        new RegexParser('/^\n/'),
                    ],
                    function ($w1, $type, $required, $w2, $var) {
                        list($variableName, $default) = $var;

                        return BlueprintVariable::create($variableName, $type, $required, $default);
                    }
                ),

                '<argument>' => new ConcParser(
                    [
                        '<variableidentifier>',
                        '<whitespacenonl>',
                        new StringParser(':'),
                        '<whitespacenonl>',
                        '<defaultvalue>',
                        '<whitespacenonl>',
                        new StringParser(';')
                    ],
                    function ($var, $w1, $colon, $w2, $val) {
                        return BlueprintArgument::create($var, $val);
                    }
                ),

                '<variabledefinition>' => new ConcParser([
                    '<variableidentifier>',
                    new LazyAltParser(
                        [
                            new ConcParser(
                                [
                                    '<whitespacenonl>',
                                    new StringParser('='),
                                    '<whitespacenonl>',
                                    '<defaultvalue>',
                                    '<whitespacenonl>',
                                    new StringParser(';')
                                ],
                                function ($w1, $eq, $w2, $default) {
                                    var_dump($default);

                                    return $default;
                                }
                            ),
                            new EmptyParser()
                        ]
                    ),
                ]),

                '<defaultvalue>' => new LazyAltParser(
                    [
                        '<number>',
                        '<boolean>',
                        '<string>',
                        '<array>',
                        '<pair>',
                    ]
                ),

                '<baredefaultvalue>' => new LazyAltParser(
                    [
                        '<barenumber>',
                        '<bareboolean>',
                        '<barestring>',
                        '<barearray>',
                        '<barepair>',
                    ]
                ),

                '<barepair>' => new ConcParser([
                    '<whitespace>',
                    new StringParser('{'),
                    '<whitespace>',
                    new LazyAltParser([
                        new ConcParser([
                            '<whitespace>',
                            '<barestring>',
                            '<whitespace>',
                            new StringParser(':'),
                            '<whitespace>',
                            '<baredefaultvalue>',
                            new GreedyStarParser(
                                new ConcParser([
                                    '<whitespace>',
                                    new StringParser(','),
                                    '<whitespace>',
                                    '<barestring>',
                                    '<whitespace>',
                                    new StringParser(':'),
                                    '<whitespace>',
                                    '<baredefaultvalue>',
                                    '<whitespace>',
                                ], $implode),
                                $implode
                            ),
                            '<whitespace>',
                        ], $implode),
                        '<whitespace>'
                    ]),
                    '<whitespace>',
                    new StringParser('}'),
                    '<whitespace>',
                ], $implode),

                '<pair>' => new ConcParser([
                    '<whitespace>',
                    new StringParser('{'),
                    '<whitespace>',
                    new LazyAltParser([
                        new ConcParser([
                            '<whitespace>',
                            '<barestring>',
                            '<whitespace>',
                            new StringParser(':'),
                            '<whitespace>',
                            '<baredefaultvalue>',
                            new GreedyStarParser(
                                new ConcParser([
                                    '<whitespace>',
                                    new StringParser(','),
                                    '<whitespace>',
                                    '<barestring>',
                                    '<whitespace>',
                                    new StringParser(':'),
                                    '<whitespace>',
                                    '<baredefaultvalue>',
                                    '<whitespace>',
                                ], $implode),
                                $implode
                            ),
                            '<whitespace>',
                        ], $implode),
                        '<whitespace>'
                    ]),
                    '<whitespace>',
                    new StringParser('}'),
                    '<whitespace>',
                ], $decodeImplode),

                '<barenumber>' => new RegexParser('/^(?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)?/six'),
                '<number>' => new RegexParser('/^(?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)?/six', $decode),

                '<bareboolean>' => new RegexParser('/^true | false | null/six'),
                '<boolean>' => new RegexParser('/^true | false | null/six', $decode),

                '<array>' => new ConcParser([
                    '<whitespace>',
                    new StringParser('['),
                    '<whitespace>',
                    new LazyAltParser([
                        new ConcParser([
                            '<baredefaultvalue>',
                            new GreedyStarParser(
                                new ConcParser([
                                    '<whitespace>',
                                    new StringParser(','),
                                    '<whitespace>',
                                    '<baredefaultvalue>',
                                    '<whitespace>',
                                ],
                                    function () {
                                        return implode('', func_get_args());
                                    }),
                                function () {
                                    return implode('', func_get_args());
                                }
                            )
                        ], $implode),
                        new EmptyParser()
                    ]),
                    '<whitespace>',
                    new StringParser(']'),
                    '<whitespace>',
                ], $decodeImplode),

                '<barearray>' => new ConcParser([
                    '<whitespace>',
                    new StringParser('['),
                    '<whitespace>',
                    new LazyAltParser([
                        new ConcParser([
                            '<baredefaultvalue>',
                            new GreedyStarParser(
                                new ConcParser([
                                    '<whitespace>',
                                    new StringParser(','),
                                    '<whitespace>',
                                    '<baredefaultvalue>',
                                    '<whitespace>',
                                ],
                                    function () {
                                        return implode('', func_get_args());
                                    }),
                                function () {
                                    return implode('', func_get_args());
                                }
                            )
                        ], $implode),
                        new EmptyParser()
                    ]),
                    '<whitespace>',
                    new StringParser(']'),
                    '<whitespace>',
                ], $implode),

                '<variableidentifier>' => new RegexParser('/^[a-zA-Z0-9-_]+/'),

                '<string>' => new RegexParser('/^" ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* "/six', $decode),
                '<barestring>' => new RegexParser('/^" ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* "/six'),

                '<whitespacenonl>' => new  RegexParser('#^[ \t]*#'),
                '<whitespace>' => new  RegexParser('#^[ \t\n]*#'),
            ),
            function ($commands) {
                return $commands;
            }
        );
    }
}
