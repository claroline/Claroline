/**
 * Created by panos on 10/29/15.
 */
(function() {
    'use strict';

    window.panels = [
        {
            id: "symbols",
            width: 334,
            visible: true,
            sections:[
                {
                    id: "basic-math",
                    children: [
                        "plus-sign", "minus-sign", "multiplication-sign", "division-sign", "middle-dot", "plus-minus-sign", "minus-plus-sign",
                        "forward-slash", "set-minus", "reverse-set-minus", "reverse-slash", "reversed-prime", "asterisk",
                        "ring-operator", "bullet", "degree-sign", "single-apostrophe", "double-apostrophe", "differential", "euler-number", "pi-number",
                        "empty-set", "increment", "nabla", "infinity", "circled-asterisk", "circled-dot", "circled-dash",
                        "circled-plus", "circled-times", "circled-division"
                    ]
                },
                {
                    id: "greek-letters",
                    children: [
                        "alpha", "beta", "gamma", "delta", "epsilon", "zeta", "eta", "theta", "theta-alt",
                        "iota", "kappa", "lambda", "mu", "nu", "xi", "omicron", "pi", "pi-alt", "rho", "sigma",
                        "final-sigma", "tau", "upsilon", "phi", "phi-alt", "chi", "psi", "omega",
                        "capital-alpha", "capital-beta", "capital-gamma", "capital-delta", "capital-epsilon", "capital-zeta",
                        "capital-eta", "capital-theta", "capital-iota", "capital-kappa", "capital-lambda", "capital-mu",
                        "capital-nu", "capital-xi", "capital-omicron", "capital-pi", "capital-rho", "capital-sigma",
                        "capital-tau", "capital-upsilon", "capital-phi", "capital-chi", "capital-psi", "capital-omega"
                    ]
                },
                {
                    id: "letter-symbols",
                    children: [
                        "alef", "ell", "real-part", "imaginary-part", "imaginary-unit-i", "quantity-j", "natural-numbers",
                        "real-numbers", "integer-numbers", "complex-numbers", "imaginary-numbers",
                        "rational-numbers", "prime-numbers", "script-capital-f", "script-capital-l", "script-capital-p", "z-transform"
                    ]
                },
                {
                    id: "relations",
                    children: [
                        "equal-operator", "identical-operator", "tilde-operator", "almost-equal", "aproximately-equal", "asymptotically-equal",
                        "less-than-sign", "less-than-or-equal", "less-than-not-equal", "less-than-or-slanted-equal",
                        "greater-than-sign", "greater-than-or-equal", "greater-than-not-equal", "greater-than-or-slanted-equal",
                        "much-greater-than", "much-less-than", "not-equal", "not-identical", "not-tilde", "not-almost-equal", "not-aproximateley-equal",
                        "proportional-to", "precedes", "succeedes"
                    ]
                },
                {
                    id: "sets",
                    children: [
                        "element-of", "not-element-of", "union", "intersection", "square-cap", "square-cup", "subset-of", "subset-of-or-equal-to",
                        "superset-of", "superset-of-or-equal-to", "square-subset-of", "square-subset-or-equal",
                        "square-superset-of", "square-superset-or-equal","for-all",
                        "not-sign", "contains-as-member", "does-not-contain-member", "normal-subgroup-of",
                        "contains-normal-subgroup", "there-exists", "there-not-exists", "logical-and",
                        "logical-or", "because", "therefore"
                    ]
                },
                {
                    id: "geometry",
                    children: [
                        "angle", "measured-angle", "spherical-angle", "circle", "triangle", "square", "parallelogram", "diamond",
                        "parallel-to", "not-parallel-to", "perpendicular"
                    ]
                }
            ]
        },
        {
            id: "radial-script-fraction",
            width: 270,
            visible: false,
            sections: [
                {
                    id: "fractions",
                    children: [
                        "fraction", "bevelled-fraction", "small-fraction", "bevelled-small-fraction"
                    ]
                },
                {
                    id: "roots",
                    children: [
                        "root", "square-root", "cube-root"
                    ]
                },
                {
                    id: "superscripts-subscripts",
                    children: [
                        "superscript", "subscript", "superscript-subscript", "element-over", "element-under", "element-underover",
                        "left-subscript", "left-superscript", "left-superscript-subscript",
                        "overscript-brace", "underscript-brace",
                        "big-operator-subscript", "big-operator-subsuperscript", "big-operator-underoverscript", "big-operator-underscript"
                    ]
                },
                {
                    id: "spaces",
                    children: [
                        "normal-space", "digit-space", "thinner-space", "back-space"
                    ]
                }
            ]
        },
        {
            id: "integrals-limit",
            width: 388,
            visible: false,
            sections: [
                {
                    id: "integrals",
                    children: [
                        "integral", "integral-subscript", "integral-subscript-differential",
                        "double-integral", "triple-integral", "contour-integral", "surface-integral", "volume-integral",
                        "definite-integral", "definite-integral-differential"
                    ]
                },
                {
                    id: "differentials",
                    children: [
                        "differential", "partial-differential", "derivative", "gradient", "partial-derivative"
                    ]
                },
                {
                    id: "limits",
                    children: [
                        "limit-infinity", "limit-underscript"
                    ]
                },
                {
                    id: "gradient-operators",
                    children: [
                        "curl", "divergence", "laplacian"
                    ]
                },
                {
                    id: "functions",
                    children: [
                        "sine", "cosine", "tangent", "secant", "cosecant", "cotangent", "arccosine", "arcsine", "arctangent", "exponential", "logarithm",
                        "logarithm-base-n", "natural-logarithm", "inverse-sine", "inverse-cosine", "inverse-tangent",
                        "inverse-cosecant", "inverse-cotangent", "inverse-secant"
                    ]
                }
            ]
        },
        {
            id: "big-operators",
            width: 235,
            visible: false,
            sections: [
                {
                    id: "summation",
                    children: [
                        "sum", "sum-subscript", "sum-subscript-superscript", "sum-underscript", "sum-underoverscript"
                    ]
                },
                {
                    id: "product",
                    children: [
                        "product", "coproduct", "product", "product-subscript", "product-subscript-superscript",
                        "product-underoverscript", "product-underscript"
                    ]
                },
                {
                    id: "union",
                    children: [
                        "big-union", "big-intersection", "big-square-cup", "big-square-cap"
                    ]
                },
            ]
        },
        {
            id: "matrices",
            width: 175,
            visible: false,
            sections: [
                {
                    id: "tables",
                    children: [
                        "table", "matrix-parenthesis", "matrix-square-brackets", "matrix-vertical-bars"
                    ]
                },
                {
                    id: "rows-columns",
                    children: [
                        "three-column-row", "three-row-column", "two-column-row-parenthesis", "two-column-row-square-bracket",
                        "two-row-column-left-curly-bracket", "two-row-column-parenthesis", "two-row-column-square-brackets",
                        "two-rows-column-right-curly-brackets"
                    ]
                },
                {
                    id: "equation-parts",
                    children: [
                        "aligned-equations", "piecewise-function"
                    ]
                }
            ]
        },
        {
            id: "decorations",
            width:205,
            visible: false,
            sections: [
                {
                    id: "parenthesis",
                    children: [
                        "parenthesis", "curly-brackets", "angle-brackets", "square-brackets", "vertical-bars",
                        "left-angle", "right-angle", "left-parenthesis", "right-parenthesis", "left-curly-bracket", "right-curly-bracket",
                        "left-square-bracket", "right-square-bracket", "double-vertical-bars", "ceeling", "floor", "angle-brackets-bar",
                    ]
                },
                {
                    id: "accents",
                    children: [
                        "arrow-accent", "vector-accent", "bar-accent", "diaeresis-accent", "dot-accent", "hat-accent", "tilde-accent",
                        "top-curly-bracket", "top-parenthesis", "bottom-curly-bracket", "bottom-parenthesis", "left-right-arrow-accent"
                    ]
                },
                {
                    id: "encloses",
                    children: [
                        "enclose-bottom", "enclose-top", "enclose-left", "enclose-right", "enclose-double-left",
                        "enclose-double-right", "enclose-box", "enclose-rounded-box", "enclose-actuarial",
                        "vertical-strike", "horizontal-strike", "horizontal-vertical-strikes", "down-diagonal-strike",
                        "up-diagonal-strike", "up-down-diagonal-strike", "enclose-circle"
                    ]
                }
            ]
        },
        {
            id: "arrows",
            width: 290,
            visible: false,
            sections: [
                {
                    id: "arrow-symbols",
                    children: [
                        "leftwards-arrow", "righwards-arrow", "downwards-arrow", "upwards-arrow", "up-down-arrow", "up-down-double-arrow",
                        "downward-left-corner-arrow", "east-west-diagonal-arrow", "west-east-diagonal-arrow",
                        "leftwards-arrow-from-bar", "leftwards-arrow-hook", "rightwards-arrow-from-bar", "rightwards-arrow-hook", "rightwards-arrow-over-leftwards-arrow",
                        "rightwards-double-arrow", "leftwards-double-arrow", "left-right-arrow", "left-right-double-arrow",
                        "leftwards-arrow-over-rightwards-arrow", "upwards-arrow-left-downwards-arrow",
                        "downwards-arrow-left-upwards-arrow", "downwards-double-arrow", "upwards-double-arrow",
                        "south-east-arrow", "south-west-arrow", "north-east-arrow", "north-west-arrow", "upwards-harpoon-left-downwards-harpoon",
                        "downwards-harpoon-left-upwards-harpoon", "leftwards-harpoon-barb-downwards", "leftwards-harpoon-barb-upwards",
                        "leftwards-harpoon-over-dash", "leftwards-harpoon-over-rightwards-harpoon",
                        "rightwards-harpoon-barb-downwards", "rightwards-harpoon-below-dash",
                        "rightwards-harpoon-over-leftwards-harpoon", "righwards-harpoon-barb-upwards"

                    ]
                },
                {
                    id: "ellipsis",
                    children: [
                        "horizontal-ellipsis", "vertical-ellipsis", "down-right-diagonal-ellipsis", "up-right-diagonal-ellipsis"
                    ]
                },
                {
                    id: "arrow-scripts",
                    children: [
                        "left-arrow-overscript", "left-arrow-underscript", "left-arrow-over-right-arrow-overscript",
                        "left-arrow-over-right-arrow-underscript", "right-left-arrow-overscript", "right-left-arrow-underscript",
                        "right-arrow-over-left-arrow-overscript", "right-arrow-over-left-arrow-underscript",
                        "right-arrow-overscript", "right-arrow-subscript",
                        "right-harpoon-over-left-harpoon-overscript", "right-harpoon-over-left-harpoon-underscript",
                        "left-harpoon-over-right-harpoon-overscript", "left-harpoon-over-right-harpoon-underscript",
                        "left-arrow-underoverscript", "left-arrow-over-right-arrow-underoverscript",
                        "left-right-arrow-underoverscript", "right-arrow-over-left-arrow-underoverscript",
                        "right-arrow-underoverscript", "right-harpoon-over-left-harpoon-underoverscript",
                        "left-harpoon-over-right-harpoon-underoverscript"
                    ]
                }
            ]
        }
    ];
}());