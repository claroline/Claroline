/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  // everything that is {{$}} is considered as variable to be filled by the user
  window.actions = [
    {"id": "alef", "mml": "<mo>&#8501;</mo>", "latex": "\\aleph"},
    {
      "id": "aligned-equations",
      "mml": "<mtable columnspacing=\"2px\" columnalign=\"right center left\"><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mo>=</mo></mtd><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mo>=</mo></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable>",
      "latex": "\\begin{align*} & {{$}} = {{$}} \\\\ & {{$}} = {{$}} \\end{align*}"
    },
    {"id": "almost-equal", "mml": "<mo>&#8776;</mo>", "latex": "\\approx"},
    {"id": "alpha", "mml": "<mi>&#945;</mi>", "latex": "\\alpha"},
    {"id": "angle", "mml": "<mo>&#8736;</mo>", "latex": "\\angle"},
    {
      "id": "angle-brackets",
      "mml": "<mfenced open=\"&lt;\" close=\"&gt;\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\langle {{$}} \\rangle"
    },
    {
      "id": "angle-brackets-bar",
      "mml": "<mfenced open=\"&lt;\" close=\"&gt;\" separators=\"|\"><mi>{{$}}</mi><mi>{{$}}</mi></mfenced>",
      "latex": "\\langle {{$}} \\mid {{$}} \\rangle"
    },
    {"id": "aproximately-equal", "mml": "<mo>&#8773;</mo>", "latex": "\\cong"},
    {
      "id": "arccosine",
      "mml": "<mi>arccos</mi><mfenced><mrow><mi>{{$}}</mi></mrow></mfenced>",
      "latex": "\\arccos({{$}})"
    },
    {
      "id": "arcsine",
      "mml": "<mi>arcsin</mi><mfenced><mrow><mi>{{$}}</mi></mrow></mfenced>",
      "latex": "\\arcsin({{$}})"
    },
    {
      "id": "arctangent",
      "mml": "<mi>arctan</mi><mfenced><mrow><mi>{{$}}</mi></mrow></mfenced>",
      "latex": "\\arctan({{$}})"
    },
    {"id": "arrow-accent", "mml": "<mover><mi>{{$}}</mi><mo>&#8594;</mo></mover>", "latex": "\\vec{{{$}}}"},
    {"id": "asterisk", "mml": "<mo>*</mo>", "latex": "\\ast"},
    {"id": "asymptotically-equal", "mml": "<mo>&#8771;</mo>", "latex": "\\simeq"},
    {"id": "back-space", "mml": "<mspace width=\"-0.2em\"/>", "latex": "\\!"},
    {"id": "bar-accent", "mml": "<mover><mi>{{$}}</mi><mo>&#175;</mo></mover>", "latex": "\\bar{{{$}}}"},
    {"id": "because", "mml": "<mo>&#8757;</mo>", "latex": "\\because"},
    {"id": "beta", "mml": "<mi>&#946;</mi>", "latex": "\\beta"},
    {"id": "bevelled-fraction", "mml": "<mfrac bevelled=\"true\"><mi>{{$}}</mi><mi>{{$}}</mi></mfrac>", "latex": ""},
    {
      "id": "bevelled-small-fraction",
      "mml": "<mstyle displaystyle=\"false\"><mfrac bevelled=\"true\"><mi>{{$}}</mi><mi>{{$}}</mi></mfrac></mstyle>",
      "latex": ""
    },
    {"id": "big-intersection", "mml": "<mo largeop=\"true\">&#8745;</mo>", "latex": "\\bigcap"},
    {"id": "big-operator-subscript", "mml": "<msub><mo largeop=\"true\">{{$}}</mo><mi>{{$}}</mi></msub>", "latex": ""},
    {
      "id": "big-operator-subsuperscript",
      "mml": "<msubsup><mo largeop=\"true\">{{$}}</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>",
      "latex": ""
    },
    {
      "id": "big-operator-underoverscript",
      "mml": "<munderover><mo largeop=\"true\">{{$}}</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": "{{$}}_{{{$}}}^{{{$}}}"
    },
    {
      "id": "big-operator-underscript",
      "mml": "<munder><mo largeop=\"true\">{{$}}</mo><mi>{{$}}</mi></munder>",
      "latex": "{{$}}_{{{$}}}"
    },
    {"id": "big-square-cap", "mml": "<mo largeop=\"true\">&#8851;</mo>", "latex": ""},
    {"id": "big-square-cup", "mml": "<mo largeop=\"true\">&#8852;</mo>", "latex": "\\bigsqcup"},
    {"id": "big-union", "mml": "<mo largeop=\"true\">&#8746;</mo>", "latex": "\\bigcup"},
    {
      "id": "bottom-curly-bracket",
      "mml": "<munder><mrow><mi>{{$}}</mi></mrow><mo>&#9183;</mo></munder>",
      "latex": "\\underbrace{{{$}}}"
    },
    {"id": "bottom-parenthesis", "mml": "<munder><mrow><mi>{{$}}</mi></mrow><mo>&#9181;</mo></munder>", "latex": ""},
    {"id": "bullet", "mml": "<mo>&#8729;</mo>", "latex": "\\bullet"},
    {"id": "capital-alpha", "mml": "<mi>&#913;</mi>", "latex": "A"},
    {"id": "capital-beta", "mml": "<mi>&#914;</mi>", "latex": "B"},
    {"id": "capital-chi", "mml": "<mi>&#935;</mi>", "latex": "X"},
    {"id": "capital-delta", "mml": "<mi>&#916;</mi>", "latex": "\\Delta"},
    {"id": "capital-epsilon", "mml": "<mi>&#917;</mi>", "latex": "E"},
    {"id": "capital-eta", "mml": "<mi>&#919;</mi>", "latex": "H"},
    {"id": "capital-gamma", "mml": "<mi>&#915;</mi>", "latex": "\\Gamma"},
    {"id": "capital-iota", "mml": "<mi>&#921;</mi>", "latex": "I"},
    {"id": "capital-kappa", "mml": "<mi>&#922;</mi>", "latex": "K"},
    {"id": "capital-lambda", "mml": "<mi>&#923;</mi>", "latex": "\\Lambda"},
    {"id": "capital-mi", "mml": "<mi>&#924;</mi>", "latex": "M"},
    {"id": "capital-ni", "mml": "<mi>&#925;</mi>", "latex": "N"},
    {"id": "capital-omega", "mml": "<mi>&#937;</mi>", "latex": "\\Omega"},
    {"id": "capital-omicron", "mml": "<mi>&#927;</mi>", "latex": "O"},
    {"id": "capital-phi", "mml": "<mi>&#934;</mi>", "latex": "\\Phi"},
    {"id": "capital-pi", "mml": "<mi>&#928;</mi>", "latex": "\\Pi"},
    {"id": "capital-psi", "mml": "<mi>&#936;</mi>", "latex": "\\Psi"},
    {"id": "capital-rho", "mml": "<mi>&#929;</mi>", "latex": "P"},
    {"id": "capital-sigma", "mml": "<mi>&#931;</mi>", "latex": "\\Sigma"},
    {"id": "capital-tau", "mml": "<mi>&#932;</mi>", "latex": "T"},
    {"id": "capital-theta", "mml": "<mi>&#920;</mi>", "latex": "\\Theta"},
    {"id": "capital-upsilon", "mml": "<mi>&#933;</mi>", "latex": "\\Upsilon"},
    {"id": "capital-xi", "mml": "<mi>&#926;</mi>", "latex": "X"},
    {"id": "capital-zeta", "mml": "<mi>&#918;</mi>", "latex": "Z"},
    {
      "id": "ceeling",
      "mml": "<mfenced open=\"&#8968;\" close=\"&#8969;\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\lceil {{$}} \\rceil"
    },
    {"id": "chi", "mml": "<mi>&#967;</mi>", "latex": "\\chi"},
    {"id": "circle", "mml": "<mo>&#9675;</mo>", "latex": ""},
    {"id": "circled-asterisk", "mml": "<mo>&#8859;</mo>", "latex": "\\circledast"},
    {"id": "circled-dash", "mml": "<mo>&#8861;</mo>", "latex": "\\circleddash"},
    {"id": "circled-division", "mml": "<mo>&#10808;</mo>", "latex": ""},
    {"id": "circled-dot", "mml": "<mo>&#8857;</mo>", "latex": "\\odot"},
    {"id": "circled-plus", "mml": "<mo>&#8853;</mo>", "latex": "\\oplus"},
    {"id": "circled-times", "mml": "<mo>&#8855;</mo>", "latex": "\\otimes"},
    {"id": "complex-numbers", "mml": "<mi>&#8450;</mi>", "latex": "\\mathbb{C}"},
    {"id": "contains-as-member", "mml": "<mo>&#8715;</mo>", "latex": "\\ni"},
    {"id": "contains-normal-subgroup", "mml": "<mo>&#8883;</mo>", "latex": "\\triangleright"},
    {"id": "contour-integral", "mml": "<mo>&#8750;</mo>", "latex": "\\oint"},
    {"id": "coproduct", "mml": "<mo>&#8720;</mo>", "latex": "\\coprod"},
    {"id": "cosecant", "mml": "<mi>csc</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\csc({{$}})"},
    {"id": "cosine", "mml": "<mi>cos</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\cos({{$}})"},
    {"id": "cotangent", "mml": "<mi>cot</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\cot({{$}})"},
    {"id": "cube-root", "mml": "<mroot><mi>{{$}}</mi><mn>3</mn></mroot>", "latex": "\\sqrt[3]{{{$}}}"},
    {"id": "curl", "mml": "<mo>&#8711;</mo><mo>&#215;</mo><mi>{{$}}</mi>", "latex": "\\nabla \\times {{$}}"},
    {
      "id": "curly-brackets",
      "mml": "<mfenced open=\"{\" close=\"}\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left \\{ {{$}} \\right \\}"
    },
    {
      "id": "definite-integral",
      "mml": "<msubsup><mo>&#8747;</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>",
      "latex": "\\int_{{{$}}}^{{{$}}}"
    },
    {
      "id": "definite-integral-differential",
      "mml": "<msubsup><mo>&#8747;</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup><mi>{{$}}</mi><mo>d</mo><mi>{{$}}</mi>",
      "latex": "\\int_{{{$}}}^{{{$}}}{{$}}\\;\\mathrm{d}{{$}}"
    },
    {"id": "degree-sign", "mml": "<mo>&#176;</mo>", "latex": "^{\\circ}"},
    {"id": "delta", "mml": "<mi>&#948;</mi>", "latex": "\\delta"},
    {
      "id": "derivative",
      "mml": "<mfrac><mrow><mo>d</mo><mi>{{$}}</mi></mrow><mrow><mo>d</mo><mi>{{$}}</mi></mrow></mfrac>",
      "latex": "\\frac{\\mathrm{d} {{$}}}{\\mathrm{d} {{$}}}"
    },
    {"id": "diaeresis-accent", "mml": "<mover><mi>{{$}}</mi><mo>&#168;</mo></mover>", "latex": "\\ddot{{{$}}}"},
    {"id": "diamond", "mml": "<mo>&#8900;</mo>", "latex": "\\diamond"},
    {"id": "differential", "mml": "<mo>d</mo>", "latex": "\\mathrm{d}"},
    {"id": "digit-space", "mml": "<mo>&#8199;</mo>", "latex": "\\;"},
    {"id": "divergence", "mml": "<mo>&#8711;</mo><mo>&#183;</mo><mi>{{$}}</mi>", "latex": "\\nabla \\cdot {{$}}"},
    {"id": "division-sign", "mml": "<mo>&#247;</mo>", "latex": "\\div"},
    {"id": "does-not-contain-member", "mml": "<mo>&#8716;</mo>", "latex": "\\not\\ni"},
    {"id": "dot-accent", "mml": "<mover><mi>{{$}}</mi><mo>&#729;</mo></mover>", "latex": "\\dot{{{$}}}"},
    {"id": "double-apostrophe", "mml": "<mo>'</mo><mo>'</mo>", "latex": "''"},
    {"id": "double-integral", "mml": "<mo>&#8748;</mo>", "latex": "\\iint"},
    {
      "id": "double-vertical-bars",
      "mml": "<mfenced open=" || " close=" || "><mi>{{$}}</mi></mfenced>",
      "latex": "\\left \\| {{$}} \\right \\|"
    },
    {
      "id": "down-diagonal-strike",
      "mml": "<menclose notation=\"downdiagonalstrike\"><mi>{{$}}</mi></menclose>",
      "latex": ""
    },
    {"id": "down-right-diagonal-ellipsis", "mml": "<mo>&#8945;</mo>", "latex": "\\ddots"},
    {"id": "downward-left-corner-arrow", "mml": "<mo>&#8629;</mo>", "latex": ""},
    {"id": "downwards-arrow", "mml": "<mo>&#8595;</mo>", "latex": "\\downarrow"},
    {"id": "downwards-arrow-left-upwards-arrow", "mml": "<mo>&#8693;</mo>", "latex": ""},
    {"id": "downwards-double-arrow", "mml": "<mo>&#8659;</mo>", "latex": "\\Downarrow"},
    {"id": "downwards-harpoon-left-upwards-harpoon", "mml": "<mo>&#10607;</mo>", "latex": ""},
    {"id": "east-west-diagonal-arrow", "mml": "<mo>&#10530;</mo>", "latex": ""},
    {"id": "element-of", "mml": "<mo>&#8712;</mo>", "latex": "\\in"},
    {"id": "element-over", "mml": "<mover><mi>{{$}}</mi><mi>{{$}}</mi></mover>", "latex": ""},
    {"id": "element-under", "mml": "<munder><mi>{{$}}</mi><mi>{{$}}</mi></munder>", "latex": ""},
    {
      "id": "element-underover",
      "mml": "<munderover><mi>{{$}}</mi><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": ""
    },
    {"id": "ell", "mml": "<mi>&#8467;</mi>", "latex": "\\ell"},
    {"id": "empty-set", "mml": "<mo>&#8709;</mo>", "latex": "\\varnothing"},
    {"id": "enclose-actuarial", "mml": "<menclose notation=\"actuarial\"><mi>{{$}}</mi></menclose>", "latex": ""},
    {
      "id": "enclose-bottom",
      "mml": "<menclose notation=\"bottom\"><mi>{{$}}</mi></menclose>",
      "latex": "\\underline{}"
    },
    {"id": "enclose-box", "mml": "<menclose notation=\"box\"><mi>{{$}}</mi></menclose>", "latex": ""},
    {"id": "enclose-circle", "mml": "<menclose notation=\"circle\"><mi>{{$}}</mi></menclose>", "latex": ""},
    {
      "id": "enclose-left",
      "mml": "<menclose notation=\"left\"><mi>{{$}}</mi></menclose>",
      "latex": "\\left | {{$}} \\right. "
    },
    {
      "id": "enclose-right",
      "mml": "<menclose notation=\"right\"><mi>{{$}}</mi></menclose>",
      "latex": "\\left. {{$}} \\right |"
    },
    {"id": "enclose-rounded-box", "mml": "<menclose notation=\"roundedbox\"><mi>{{$}}</mi></menclose>", "latex": ""},
    {"id": "enclose-top", "mml": "<menclose notation=\"top\"><mi>{{$}}</mi></menclose>", "latex": "\\overline{{{$}}}"},
    {"id": "epsilon", "mml": "<mi>&#949;</mi>", "latex": "\\epsilon"},
    {"id": "equal-operator", "mml": "<mo>=</mo>", "latex": "="},
    {"id": "eta", "mml": "<mi>&#951;</mi>", "latex": "\\eta"},
    {"id": "euler-number", "mml": "<mi>e</mi>", "latex": "e"},
    {"id": "exp", "mml": "<msup><mi>e</mi><mi>{{$}}</mi></msup>", "latex": "e^{{{$}}}"},
    {"id": "exponential", "mml": "<mi>exp</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\exp({{$}})"},
    {"id": "final-sigma", "mml": "<mi>&#962;</mi>", "latex": "\\varsigma"},
    {
      "id": "floor",
      "mml": "<mfenced open=\"&#8970;\" close=\"&#8971;\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\lfloor {{$}} \\rfloor"
    },
    {"id": "for-all", "mml": "<mo>&#8704;</mo>", "latex": "\\forall"},
    {"id": "forward-slash", "mml": "<mo>/</mo>", "latex": "/"},
    {"id": "fraction", "mml": "<mfrac><mi>{{$}}</mi><mi>{{$}}</mi></mfrac>", "latex": "\\frac{{{$}}}{{{$}}}"},
    {"id": "gamma", "mml": "<mi>&#947;</mi>", "latex": "\\gamma"},
    {"id": "gradient", "mml": "<mo>&#8711;</mo><mi>{{$}}</mi>", "latex": "\\nabla {{$}}"},
    {"id": "greater-than-not-equal", "mml": "<mo>&#10888;</mo>", "latex": "\\gneq"},
    {"id": "greater-than-or-equal", "mml": "<mo>&#8805;</mo>", "latex": "\\geq "},
    {"id": "greater-than-or-slanted-equal", "mml": "<mo>&#10878;</mo>", "latex": "\\geqslant"},
    {"id": "greater-than-sign", "mml": "<mo>&gt;</mo>", "latex": ">"},
    {"id": "hat-accent", "mml": "<mover><mi>{{$}}</mi><mo>^</mo></mover>", "latex": "\\hat{{{$}}}"},
    {"id": "horizontal-ellipsis", "mml": "<mo>&#8943;</mo>", "latex": "\\cdots"},
    {
      "id": "horizontal-strike",
      "mml": "<menclose notation=\"horizontalstrike\"><mi>{{$}}</mi></menclose>",
      "latex": ""
    },
    {
      "id": "horizontal-vertical-strikes",
      "mml": "<menclose notation=\"verticalstrike horizontalstrike\"><mi>{{$}}</mi></menclose>",
      "latex": ""
    },
    {"id": "identical-operator", "mml": "<mo>&#8801;</mo>", "latex": "\\equiv"},
    {"id": "imaginary-numbers", "mml": "<mi mathvariant=\"normal\">&#120128;</mi>", "latex": "\\mathbb{I}"},
    {"id": "imaginary-part", "mml": "<mo>&#8465;</mo>", "latex": "\\Im"},
    {"id": "imaginary-unit-i", "mml": "<mi>i</mi>", "latex": "i"},
    {"id": "increment", "mml": "<mo>&#8710;</mo>", "latex": "\\triangle"},
    {"id": "infinity", "mml": "<mo>&#8734;</mo>", "latex": "\\infty"},
    {"id": "integer-numbers", "mml": "<mi mathvariant=\"normal\">&#8484;</mi>", "latex": "\\mathbb{Z}"},
    {"id": "integral", "mml": "<mo>&#8747;</mo>", "latex": "\\int"},
    {"id": "integral-subscript", "mml": "<msub><mo>&#8747;</mo><mi>{{$}}</mi></msub>", "latex": "\\int_{{$}}"},
    {
      "id": "integral-subscript-differential",
      "mml": "<msub><mo>&#8747;</mo><mi>{{$}}</mi></msub><mi>{{$}}</mi><mo>d</mo><mi>{{$}}</mi>",
      "latex": "\\int_{{$}}{{$}}\\;\\mathrm{d}{{$}}"
    },
    {"id": "intersection", "mml": "<mo>&#8745;</mo>", "latex": "\\cap"},
    {
      "id": "inverse-cosecant",
      "mml": "<msup><mi>csc</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": "\\csc^{-1}(x)"
    },
    {
      "id": "inverse-cosine",
      "mml": "<msup><mi>cos</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": "\\cos^{-1}(x)"
    },
    {
      "id": "inverse-cotangent",
      "mml": "<msup><mi>cot</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": "\\cot^{-1}(x)"
    },
    {
      "id": "inverse-secant",
      "mml": "<msup><mi>sec</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": "\\sec^{-1}(x)"
    },
    {
      "id": "inverse-sine",
      "mml": "<msup><mi>sin</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": "\\sin^{-1}(x)"
    },
    {
      "id": "inverse-tangent",
      "mml": "<msup><mi>tan</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": "\\tan^{-1}(x)"
    },
    {"id": "iota", "mml": "<mi>&#953;</mi>", "latex": "\\iota"},
    {"id": "kappa", "mml": "<mi>&#954;</mi>", "latex": "\\kappa"},
    {"id": "lambda", "mml": "<mi>&#955;</mi>", "latex": "\\lambda"},
    {"id": "laplacian", "mml": "<mo>&#8710;</mo><mi>{{$}}</mi>", "latex": "\\Delta {{$}}"},
    {
      "id": "left-angle",
      "mml": "<mfenced open=\"&lt;\" close=\"\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left \\langle {{$}} \\right."
    },
    {
      "id": "left-arrow-over-right-arrow-overscript",
      "mml": "<mover><mo>&#8646;</mo><mi>{{$}}</mi></mover>",
      "latex": ""
    },
    {
      "id": "left-arrow-over-right-arrow-underscript",
      "mml": "<munder><mo>&#8646;</mo><mi>{{$}}</mi></munder>",
      "latex": ""
    },
    {
      "id": "left-arrow-over-right-arrow-underoverscript",
      "mml": "<munderover><mo>&#8646;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": ""
    },
    {
      "id": "left-arrow-overscript",
      "mml": "<mover><mo>&#8592;</mo><mi>{{$}}</mi></mover>",
      "latex": "\\overset{{{$}}}{\\leftarrow}"
    },
    {
      "id": "left-arrow-underscript",
      "mml": "<munder><mo>&#8592;</mo><mi>{{$}}</mi></munder>",
      "latex": "\\underset{{{$}}}{\\leftarrow}"
    },
    {
      "id": "left-arrow-underoverscript",
      "mml": "<munderover><mo>&#8592;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": "\\xleftarrow[{{$}}]{{{$}}}"
    },
    {
      "id": "left-curly-bracket",
      "mml": "<mfenced open=\"{\" close=\"\"><mi>a</mi></mfenced>",
      "latex": "\\left \\{ {{$}} \\right."
    },
    {
      "id": "left-harpoon-over-right-harpoon-overscript",
      "mml": "<mover><mo>&#8651;</mo><mi>{{$}}</mi></mover>",
      "latex": "\\overset{{{$}}}{\\leftrightharpoons}"
    },
    {
      "id": "left-harpoon-over-right-harpoon-underscript",
      "mml": "<munder><mo>&#8651;</mo><mi>{{$}}</mi></munder>",
      "latex": "\\underset{{{$}}}{\\leftrightharpoons}"
    },
    {
      "id": "left-harpoon-over-right-harpoon-underoverscript",
      "mml": "<munderover><mo>&#8651;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": "\\overset{{{$}}}{\\underset{{{$}}}{\\leftrightharpoons}}"
    },
    {
      "id": "left-parenthesis",
      "mml": "<mfenced close=\"\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left ( {{$}} \\right."
    },
    {"id": "left-right-arrow", "mml": "<mo>&#8596;</mo>", "latex": "\\leftrightarrow"},
    {
      "id": "left-right-arrow-accent",
      "mml": "<mover><mi>{{$}}</mi><mo>&#8596;</mo></mover>",
      "latex": "\\overset{\\leftrightarrow}{{{$}}}"
    },
    {
      "id": "left-right-arrow-underoverscript",
      "mml": "<munderover><mo>&#8596;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": "\\overset{{{$}}}{\\underset{{{$}}}{\\leftrightarrow}}"
    },
    {"id": "left-right-double-arrow", "mml": "<mo>&#8660;</mo>", "latex": "\\Leftrightarrow"},
    {
      "id": "left-square-bracket",
      "mml": "<mfenced open=\"[\" close=\"\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left [ {{$}} \\right."
    },
    {
      "id": "left-subscript",
      "mml": "<mmultiscripts><mi>{{$}}</mi><mprescripts/><mi>{{$}}</mi><none/></mmultiscripts>",
      "latex": " _{{{$}}}{{$}}"
    },
    {
      "id": "left-superscript",
      "mml": "<mmultiscripts><mi>{{$}}</mi><mprescripts/><none/><mi>{{$}}</mi></mmultiscripts>",
      "latex": " ^{{{$}}}{{$}}"
    },
    {
      "id": "left-superscript-subscript",
      "mml": "<mmultiscripts><mi>{{$}}</mi><mprescripts/><mi>{{$}}</mi><mi>{{$}}</mi></mmultiscripts>",
      "latex": "_{{{$}}}^{{{$}}}{{$}}"
    },
    {"id": "leftwards-arrow", "mml": "<mo>&#8592;</mo>", "latex": "\\leftarrow"},
    {"id": "leftwards-arrow-from-bar", "mml": "<mo>&#8612;</mo>", "latex": ""},
    {"id": "leftwards-arrow-hook", "mml": "<mo>&#8617;</mo>", "latex": "\\hookleftarrow"},
    {"id": "leftwards-arrow-over-rightwards-arrow", "mml": "<mo>&#8646;</mo>", "latex": ""},
    {"id": "leftwards-double-arrow", "mml": "<mo>&#8656;</mo>", "latex": "\\Leftarrow"},
    {"id": "leftwards-harpoon-barb-downwards", "mml": "<mo>&#8637;</mo>", "latex": "\\leftharpoondown"},
    {"id": "leftwards-harpoon-barb-upwards", "mml": "<mo>&#8636;</mo>", "latex": "\\leftharpoonup"},
    {"id": "leftwards-harpoon-over-dash", "mml": "<mo>&#10602;</mo>", "latex": ""},
    {"id": "leftwards-harpoon-over-rightwards-harpoon", "mml": "<mo>&#8651;</mo>", "latex": "\\leftrightharpoons"},
    {"id": "less-than-not-equal", "mml": "<mo>&#10887;</mo>", "latex": "\\lneq"},
    {"id": "less-than-or-equal", "mml": "<mo>&#8804;</mo>", "latex": "\\leq"},
    {"id": "less-than-or-slanted-equal", "mml": "<mo>&#10877;</mo>", "latex": "\\leqslant"},
    {"id": "less-than-sign", "mml": "<mo>&lt;</mo>", "latex": "<"},
    {
      "id": "limit-infinity",
      "mml": "<munder><mrow><mi>lim</mi></mrow><mrow><mi>{{$}}</mi><mo>&#8594;</mo><mo>&#8734;</mo></mrow></munder>",
      "latex": "\\lim_{{{$}}\\rightarrow \\infty}"
    },
    {
      "id": "limit-underscript",
      "mml": "<munder><mrow><mi>lim</mi></mrow><mi>{{$}}</mi></munder>",
      "latex": "\\lim_{{{$}}}"
    },
    {"id": "logarithm", "mml": "<mi>log</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\log({{$}})"},
    {
      "id": "logarithm-base-n",
      "mml": "<msub><mi>log</mi><mi>n</mi></msub><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": "\\log_{n}({{$}})"
    },
    {"id": "logical-and", "mml": "<mo>&#8743;</mo>", "latex": "\\land"},
    {"id": "logical-or", "mml": "<mo>&#8744;</mo>", "latex": "\\lor"},
    {
      "id": "matrix-parenthesis",
      "matrix": true,
      "mml": "<mfenced><mtable>{{$}}</mtable></mfenced>",
      "latex": "\\begin{pmatrix}{{$}}\\end{pmatrix}"
    },
    {
      "id": "matrix-square-brackets",
      "matrix": true,
      "mml": "<mfenced open=\"[\" close=\"]\"><mtable>{{$}}</mtable></mfenced>",
      "latex": "\\begin{bmatrix}{{$}}\\end{bmatrix}"
    },
    {
      "id": "matrix-vertical-bars",
      "matrix": true,
      "mml": "<mfenced open=\"|\" close=\"|\"><mtable>{{$}}</mtable></mfenced>",
      "latex": "\\begin{vmatrix}{{$}}\\end{vmatrix}"
    },
    {"id": "measured-angle", "mml": "<mo>&#8737;</mo>", "latex": "\\measuredangle"},
    {"id": "middle-dot", "mml": "<mo>&#183;</mo>", "latex": "\\cdot"},
    {"id": "minus-plus-sign", "mml": "<mo>&#8723;</mo>", "latex": "\\mp"},
    {"id": "minus-sign", "mml": "<mo>-</mo>", "latex": " - "},
    {"id": "mu", "mml": "<mi>&#956;</mi>", "latex": "\\mu"},
    {"id": "much-greater-than", "mml": "<mo>&#8811;</mo>", "latex": "\\gg"},
    {"id": "much-less-than", "mml": "<mo>&#8810;</mo>", "latex": "\\ll"},
    {"id": "multiplication-sign", "mml": "<mo>&#215;</mo>", "latex": "\\times"},
    {"id": "nabla", "mml": "<mo>&#8711;</mo>", "latex": "\\nabla"},
    {
      "id": "natural-logarithm",
      "mml": "<mo>ln</mo><mfenced><mi>{{$}}</mi></mfenced>",
      "latex": " \\ln\\left ( {{$}} \\right ) "
    },
    {"id": "natural-numbers", "mml": "<mi mathvariant=\"normal\">&#8469;</mi>", "latex": "\\mathbb{I} "},
    {"id": "normal-space", "mml": "<mo>&#160;</mo>", "latex": "\\:"},
    {"id": "normal-subgroup-of", "mml": "<mo>&#8882;</mo>", "latex": "\\lhd "},
    {"id": "north-east-arrow", "mml": "<mo>&#8599;</mo>", "latex": "\\nearrow "},
    {"id": "north-west-arrow", "mml": "<mo>&#8598;</mo>", "latex": "\\nwarrow "},
    {"id": "not-almost-equal", "mml": "<mo>&#8777;</mo>", "latex": "\\not\\approx "},
    {"id": "not-aproximateley-equal", "mml": "<mo>&#x2247;</mo>", "latex": "\\not\\cong "},
    {"id": "not-element-of", "mml": "<mo>&#8713;</mo>", "latex": "\\notin "},
    {"id": "not-equal", "mml": "<mo>&#8800;</mo>", "latex": "\\neq"},
    {"id": "not-identical", "mml": "<mo>&#8802;</mo>", "latex": "\\not\\equiv"},
    {"id": "not-parallel-to", "mml": "<mo>&#8742;</mo>", "latex": "\\nparallel"},
    {"id": "not-sign", "mml": "<mo>&#172;</mo>", "latex": "\\neg"},
    {"id": "not-tilde", "mml": "<mo>&#8769;</mo>", "latex": "\\nsim"},
    {"id": "nu", "mml": "<mi>&#956;</mi>", "latex": "\\nu"},
    {"id": "omega", "mml": "<mi>&#969;</mi>", "latex": "\\omega"},
    {"id": "omicron", "mml": "<mi>&#959;</mi>", "latex": "o"},
    {
      "id": "overscript-brace",
      "mml": "<mover><mover><mi>{{$}}</mi><mo>&#9182;</mo></mover><mi>{{$}}</mi></mover>",
      "latex": "\\overbrace{{{$}}}^{{{$}}}"
    },
    {"id": "parallel-to", "mml": "<mo>&#8741;</mo>", "latex": "\\parallel"},
    {"id": "parallelogram", "mml": "<mo>&#9649;</mo>", "latex": ""},
    {"id": "parenthesis", "mml": "<mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\left ( {{$}} \\right )"},
    {
      "id": "partial-derivative",
      "mml": "<mfrac><mrow><mo>&#8706;</mo><mi>{{$}}</mi></mrow><mrow><mo>&#8706;</mo><mi>{{$}}</mi></mrow></mfrac>",
      "latex": "\\frac{\\partial {{$}}}{\\partial {{$}}}"
    },
    {
      "id": "partial-differential",
      "mml": "<mfrac><mrow><mo>d</mo><mi>{{$}}</mi></mrow><mrow><mo>d</mo><mi>{{$}}</mi></mrow></mfrac>",
      "latex": "\\frac{\\mathrm{d} {{$}}}{\\mathrm{d} {{$}}}"
    },
    {"id": "perpendicular", "mml": "<mo>&#8869;</mo>", "latex": "\\perp"},
    {"id": "phi", "mml": "<mi>&#966;</mi>", "latex": "\\phi"},
    {"id": "phi-alt", "mml": "<mi>&#981;</mi>", "latex": "\\varphi"},
    {"id": "pi", "mml": "<mi>&#960;</mi>", "latex": "\\pi"},
    {"id": "pi-alt", "mml": "<mi>&#982;</mi>", "latex": "\\varpi"},
    {"id": "pi-number", "mml": "<mi mathvariant=\"normal\">&#960;</mi>", "latex": "\\pi"},
    {
      "id": "piecewise-function",
      "mml": "<mfenced open=\"{\" close=\"\"><mtable columnspacing=\"1.4ex\" columnalign=\"left\"><mtr><mtd><mi mathvariant=\"normal\">{{$}}</mi></mtd><mtd><mi mathvariant=\"normal\">{{$}}</mi></mtd></mtr><mtr><mtd><mi mathvariant=\"normal\">{{$}}</mi></mtd><mtd><mi mathvariant=\"normal\">{{$}}</mi></mtd></mtr></mtable></mfenced>",
      "latex": "\\left\\{\\begin{matrix} {{$}} & {{$}} \\\\ {{$}} & {{$}} \\end{matrix}\\right."
    },
    {"id": "plus-minus-sign", "mml": "<mo>&#177;</mo>", "latex": "\\pm"},
    {"id": "plus-sign", "mml": "<mo>+</mo>", "latex": "+"},
    {"id": "precedes", "mml": "<mo>&#8826;</mo>", "latex": "\\prec"},
    {"id": "prime-numbers", "mml": "<mi mathvariant=\"normal\">&#8473;</mi>", "latex": "\\mathbb{P}"},
    {"id": "product", "mml": "<mo>&#8719;</mo>", "latex": "\\prod"},
    {
      "id": "product-subscript",
      "mml": "<msub><mo>&#8719;</mo><mi mathvariant=\"normal\">{{$}}</mi></msub>",
      "latex": ""
    },
    {
      "id": "product-subscript-superscript",
      "mml": "<msubsup><mo>&#8719;</mo><mi mathvariant=\"normal\">{{$}}</mi><mi mathvariant=\"normal\">{{$}}</mi></msubsup>",
      "latex": ""
    },
    {
      "id": "product-underoverscript",
      "mml": "<munderover><mo>&#8719;</mo><mi mathvariant=\"normal\">{{$}}</mi><mi mathvariant=\"normal\">{{$}}</mi></munderover>",
      "latex": "\\prod_{{{$}}}^{{{$}}}"
    },
    {
      "id": "product-underscript",
      "mml": "<munder><mo>&#8719;</mo><mi mathvariant=\"normal\">{{$}}</mi></munder>",
      "latex": "\\prod_{{{$}}}"
    },
    {"id": "proportional-to", "mml": "<mo>&#8733;</mo>", "latex": "\\propto"},
    {"id": "psi", "mml": "<mi mathvariant=\"normal\">&#968;</mi>", "latex": "\\psi"},
    {"id": "quantity-j", "mml": "<mi>j</mi>", "latex": "\\jmath"},
    {"id": "rational-numbers", "mml": "<mi mathvariant=\"normal\">&#8474;</mi>", "latex": "\\mathbb{Q}"},
    {"id": "real-numbers", "mml": "<mi mathvariant=\"normal\">&#8477;</mi>", "latex": "\\mathbb{R}"},
    {"id": "real-part", "mml": "<mo>&#8476;</mo>", "latex": "\\Re"},
    {"id": "reverse-set-minus", "mml": "<mo>&#8726;</mo>", "latex": ""},
    {"id": "reverse-slash", "mml": "<mo>\</mo>", "latex": "\\setminus"},
    {"id": "reversed-prime", "mml": "<mo>&#8245;</mo>", "latex": "\\prime"},
    {"id": "rho", "mml": "<mi>&#961;</mi>", "latex": "\\rho"},
    {
      "id": "right-angle",
      "mml": "<mfenced open=\"\" close=\"&gt;\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left. {{$}} \\right \\rangle"
    },
    {
      "id": "right-arrow-over-left-arrow-overscript",
      "mml": "<mover><mo>&#8644;</mo><mi>{{$}}</mi></mover>",
      "latex": ""
    },
    {
      "id": "right-arrow-over-left-arrow-underscript",
      "mml": "<munder><mo>&#8644;</mo><mi>{{$}}</mi></munder>",
      "latex": ""
    },
    {
      "id": "right-arrow-over-left-arrow-underoverscript",
      "mml": "<munderover><mo>&#8644;</mo><mi>b</mi><mi>{{$}}</mi></munderover>",
      "latex": ""
    },
    {
      "id": "right-arrow-overscript",
      "mml": "<mover><mo>&#8594;</mo><mi>{{$}}</mi></mover>",
      "latex": "\\overset{{{$}}}{\\rightarrow}"
    },
    {
      "id": "right-arrow-subscript",
      "mml": "<munder><mo>&#8594;</mo><mi>{{$}}</mi></munder>",
      "latex": "\\underset{{{$}}}{\\rightarrow}"
    },
    {
      "id": "right-arrow-underoverscript",
      "mml": "<munderover><mo>&#8594;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": "\\xrightarrow[{{$}}]{{{$}}}"
    },
    {
      "id": "right-curly-bracket",
      "mml": "<mfenced open=\"\" close=\"}\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left. {{$}} \\right \\}"
    },
    {
      "id": "right-harpoon-over-left-harpoon-overscript",
      "mml": "<mover><mo>&#8652;</mo><mi>{{$}}</mi></mover>",
      "latex": "\\overset{{{$}}}{\\rightleftharpoons}"
    },
    {
      "id": "right-harpoon-over-left-harpoon-underoverscript",
      "mml": "<munderover><mo>&#8652;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": "\\overset{{{$}}}{\\underset{{{$}}}{\\rightleftharpoons}}"
    },
    {
      "id": "right-harpoon-over-left-harpoon-underscript",
      "mml": "<munder><mo>&#8652;</mo><mi>{{$}}</mi></munder>",
      "latex": "\\underset{{{$}}}{\\rightleftharpoons}"
    },
    {
      "id": "right-left-arrow-overscript",
      "mml": "<mover><mo>&#8596;</mo><mi>{{$}}</mi></mover>",
      "latex": "\\overset{{{$}}}{\\leftrightarrow}"
    },
    {
      "id": "right-left-arrow-underscript",
      "mml": "<munder><mo>&#8596;</mo><mi>{{$}}</mi></munder>",
      "latex": "\\underset{{{$}}}{\\leftrightarrow}"
    },
    {
      "id": "right-parenthesis",
      "mml": "<mfenced open=\"\" close=\")\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left. {{$}} \\right )"
    },
    {
      "id": "right-square-bracket",
      "mml": "<mfenced open=\"\" close=\"]\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left. {{$}} \\right ]"
    },
    {"id": "rightwards-arrow-from-bar", "mml": "<mo>&#8614;</mo>", "latex": "\\mapsto"},
    {"id": "rightwards-arrow-hook", "mml": "<mo>&#8618;</mo>", "latex": "\\hookrightarrow"},
    {"id": "rightwards-arrow-over-leftwards-arrow", "mml": "<mo>&#8644;</mo>", "latex": ""},
    {"id": "rightwards-double-arrow", "mml": "<mo>&#8658;</mo>", "latex": "\\Rightarrow"},
    {"id": "rightwards-harpoon-barb-downwards", "mml": "<mo>&#8641;</mo>", "latex": "\\rightharpoondown"},
    {"id": "rightwards-harpoon-below-dash", "mml": "<mo>&#10605;</mo>", "latex": ""},
    {"id": "rightwards-harpoon-over-leftwards-harpoon", "mml": "<mo>&#8652;</mo>", "latex": "\\rightleftharpoons"},
    {"id": "righwards-arrow", "mml": "<mo>&#8594;</mo>", "latex": "\\rightarrow"},
    {"id": "righwards-harpoon-barb-upwards", "mml": "<mo>&#8640;</mo>", "latex": "\\rightharpoonup"},
    {"id": "ring-operator", "mml": "<mo>&#8728;</mo>", "latex": "\\circ"},
    {"id": "root", "mml": "<mroot><mi>{{$}}</mi><mn>{{$}}</mn></mroot>", "latex": "\\sqrt[{{$}}]{{{$}}}"},
    {"id": "script-capital-f", "mml": "<mo>&#8497;</mo>", "latex": "\\mathcal{F}"},
    {"id": "script-capital-l", "mml": "<mo>&#8466;</mo>", "latex": "\\mathcal{L}"},
    {"id": "script-capital-p", "mml": "<mi>&#8472;</mi>", "latex": "\\wp"},
    {"id": "secant", "mml": "<mi>sec</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\sec({{$}})"},
    {"id": "sigma", "mml": "<mi>&#963;</mi>", "latex": "\\sigma"},
    {"id": "sine", "mml": "<mi>sin</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\sin({{$}})"},
    {"id": "single-apostrophe", "mml": "<mo>'</mo>", "latex": "'"},
    {
      "id": "small-fraction",
      "mml": "<mstyle displaystyle=\"false\"><mfrac><mi>{{$}}</mi><mi>{{$}}</mi></mfrac></mstyle>",
      "latex": "\\tfrac{{{$}}}{{{$}}}"
    },
    {"id": "south-east-arrow", "mml": "<mo>&#8600;</mo>", "latex": "\\searrow"},
    {"id": "south-west-arrow", "mml": "<mo>&#8601;</mo>", "latex": "\\swarrow"},
    {"id": "spherical-angle", "mml": "<mo>&#8738;</mo>", "latex": "\\sphericalangle"},
    {"id": "square", "mml": "<mo>&#9633;</mo>", "latex": "\\square"},
    {
      "id": "square-brackets",
      "mml": "<mfenced open=\"[\" close=\"]\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left [ {{$}} \\right ]"
    },
    {"id": "square-cap", "mml": "<mo>&#8851;</mo>", "latex": "\\sqcap"},
    {"id": "square-cup", "mml": "<mo>&#8852;</mo>", "latex": "\\sqcup"},
    {"id": "square-root", "mml": "<msqrt><mi>{{$}}</mi></msqrt>", "latex": "\\sqrt{{{$}}}"},
    {"id": "square-subset-of", "mml": "<mo>&#8847;</mo>", "latex": "\\sqsubset"},
    {"id": "square-subset-or-equal", "mml": "<mo>&#8849;</mo>", "latex": "\\sqsubseteq"},
    {"id": "square-superset-of", "mml": "<mo>&#8848;</mo>", "latex": "\\sqsupset"},
    {"id": "square-superset-or-equal", "mml": "<mo>&#8850;</mo>", "latex": "\\sqsupseteq"},
    {"id": "subscript", "mml": "<msub><mi>{{$}}</mi><mi>{{$}}</mi></msub>", "latex": "{{$}}_{{{$}}}"},
    {"id": "subset-of", "mml": "<mo>&#8834;</mo>", "latex": "\\subset"},
    {"id": "subset-of-or-equal-to", "mml": "<mo>&#8838;</mo>", "latex": "\\subseteq"},
    {"id": "succeedes", "mml": "<mo>&#8827;</mo>", "latex": "\\succ"},
    {"id": "sum", "mml": "<mo>&#8721;</mo>", "latex": "\\sum"},
    {"id": "sum-subscript", "mml": "<msub><mo>&#8721;</mo><mi>{{$}}</mi></msub>", "latex": ""},
    {
      "id": "sum-subscript-superscript",
      "mml": "<msubsup><mo>&#8721;</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>",
      "latex": ""
    },
    {
      "id": "sum-underoverscript",
      "mml": "<munderover><mo>&#8721;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>",
      "latex": "\\sum_{{{$}}}^{{{$}}}"
    },
    {"id": "sum-underscript", "mml": "<munder><mo>&#8721;</mo><mi>{{$}}</mi></munder>", "latex": "\\sum_{{{$}}}"},
    {"id": "superscript", "mml": "<msup><mi>{{$}}</mi><mi>{{$}}</mi></msup>", "latex": "{{$}}^{{{$}}}"},
    {
      "id": "superscript-subscript",
      "mml": "<msubsup><mi>{{$}}</mi><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>",
      "latex": "{{$}}_{{{$}}}^{{{$}}}"
    },
    {"id": "superset-of", "mml": "<mo>&#8835;</mo>", "latex": "\\supset"},
    {"id": "superset-of-or-equal-to", "mml": "<mo>&#8839;</mo>", "latex": "\\supseteq"},
    {"id": "surface-integral", "mml": "<mo>&#8751;</mo>", "latex": "\\oint"},
    {"id": "table", "mml": "<mtable>{{$}}</mtable>", "matrix": true, "latex": "\\begin{matrix}{{$}}\\end{matrix}"},
    {"id": "tangent", "mml": "<mi>tan</mi><mfenced><mi>{{$}}</mi></mfenced>", "latex": "\\tan({{$}})"},
    {"id": "tau", "mml": "<mi>&#964;</mi>", "latex": "\\tau"},
    {"id": "there-exists", "mml": "<mo>&#8707;</mo>", "latex": "\\exists"},
    {"id": "there-not-exists", "mml": "<mo>&#8708;</mo>", "latex": "\\nexists"},
    {"id": "therefore", "mml": "<mo>&#8756;</mo>", "latex": "\\therefore"},
    {"id": "theta", "mml": "<mi>&#952;</mi>", "latex": "\\theta"},
    {"id": "theta-alt", "mml": "<mi>&#977;</mi>", "latex": "\\vartheta"},
    {"id": "thinner-space", "mml": "<mo>&#8202;</mo>", "latex": "\\,"},
    {
      "id": "three-column-row",
      "mml": "<mtable><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable>",
      "latex": "\\begin{matrix} {{$}} & {{$}} & {{$}} \\end{matrix}"
    },
    {
      "id": "three-row-column",
      "mml": "<mtable><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable>",
      "latex": "\\begin{matrix} {{$}} \\\\ {{$}} \\\\ {{$}} \\end{matrix}"
    },
    {"id": "tilde-accent", "mml": "<mover><mi>{{$}}</mi><mo>~</mo></mover>", "latex": "\\widetilde{{{$}}}"},
    {"id": "tilde-operator", "mml": "<mo>~</mo>", "latex": "\\sim"},
    {
      "id": "top-curly-bracket",
      "mml": "<mover><mrow><mi>{{$}}</mi></mrow><mo>&#9182;</mo></mover>",
      "latex": "\\overbrace{{{$}}}"
    },
    {"id": "top-parenthesis", "mml": "<mover><mi>{{$}}</mi><mo>&#9180;</mo></mover>", "latex": ""},
    {"id": "triangle", "mml": "<mo>&#9651;</mo>", "latex": "\\triangle"},
    {"id": "triple-integral", "mml": "<mo>&#8749;</mo>", "latex": "\\iiint"},
    {
      "id": "two-column-row-parenthesis",
      "mml": "<mfenced><mtable><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>",
      "latex": "\\begin{pmatrix} {{$}} & {{$}} \\end{pmatrix}"
    },
    {
      "id": "two-column-row-square-bracket",
      "mml": "<mfenced open=\"[\" close=\"]\"><mtable><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>",
      "latex": "\\begin{bmatrix} {{$}} & {{$}} \\end{bmatrix}"
    },
    {
      "id": "two-row-column-left-curly-bracket",
      "mml": "<mfenced open=\"{\" close=\"\"><mtable columnalign=\"left\"><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>",
      "latex": "\\left\\{\\begin{matrix} {{$}} \\\\ {{$}} \\end{matrix}\\right."
    },
    {
      "id": "two-row-column-parenthesis",
      "mml": "<mfenced><mtable><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>",
      "latex": "\\binom{{{$}}}{{{$}}}"
    },
    {
      "id": "two-row-column-square-brackets",
      "mml": "<mfenced open=\"[\" close=\"]\"><mtable><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>",
      "latex": "\\begin{bmatrix} {{$}} \\\\ {{$}} \\end{bmatrix}"
    },
    {
      "id": "two-rows-column-right-curly-brackets",
      "mml": "<mfenced open=\"\" close=\"}\"><mtable columnalign=\"right\"><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>",
      "latex": "\\left.\\begin{matrix} {{$}} \\ {{$}} \\end{matrix}\\right\\}"
    },
    {
      "id": "underscript-brace",
      "mml": "<munder><munder><mi>{{$}}</mi><mo>&#9183;</mo></munder><mrow><mi>{{$}}</mi></mrow></munder>",
      "latex": "\\underbrace{{{$}}}_{{{$}}}"
    },
    {"id": "union", "mml": "<mo>&#8746;</mo>", "latex": "\\cup"},
    {
      "id": "up-diagonal-strike",
      "mml": "<menclose notation=\"updiagonalstrike\"><mi>{{$}}</mi></menclose>",
      "latex": ""
    },
    {"id": "up-down-arrow", "mml": "<mo>&#8597;</mo>", "latex": "\\updownarrow"},
    {
      "id": "up-down-diagonal-strike",
      "mml": "<menclose notation=\"downdiagonalstrike updiagonalstrike\"><mi>{{$}}</mi></menclose>",
      "latex": ""
    },
    {"id": "up-down-double-arrow", "mml": "<mo>&#8661;</mo>", "latex": "\\Updownarrow"},
    {"id": "up-right-diagonal-ellipsis", "mml": "<mo>&#8944;</mo>", "latex": ""},
    {"id": "upsilon", "mml": "<mi>&#965;</mi>", "latex": "\\upsilon"},
    {"id": "upwards-arrow", "mml": "<mo>&#8593;</mo>", "latex": "\\uparrow"},
    {"id": "upwards-arrow-left-downwards-arrow", "mml": "<mo>&#8645;</mo>", "latex": "\\uparrow\\!\\downarrow"},
    {"id": "upwards-double-arrow", "mml": "<mo>&#8657;</mo>", "latex": "\\Uparrow"},
    {"id": "upwards-harpoon-left-downwards-harpoon", "mml": "<mo>&#10606;</mo>", "latex": ""},
    {"id": "vector-accent", "mml": "<mover><mi>{{$}}</mi><mo>&#8640;</mo></mover>", "latex": "\\vec{{{$}}}"},
    {
      "id": "vertical-bars",
      "mml": "<mfenced open=\"|\" close=\"|\"><mi>{{$}}</mi></mfenced>",
      "latex": "\\left | {{$}} \\right |"
    },
    {"id": "vertical-ellipsis", "mml": "<mo>&#8942;</mo>", "latex": "\\vdots"},
    {"id": "vertical-strike", "mml": "<menclose notation=\"horizontalstrike\"><mi>{{$}}</mi></menclose>", "latex": ""},
    {"id": "volume-integral", "mml": "<mo>&#8752;</mo>", "latex": ""},
    {"id": "west-east-diagonal-arrow", "mml": "<mo>&#10529;</mo>", "latex": "\\wearrow"},
    {"id": "xi", "mml": "<mi>&#958;</mi>", "latex": "\\xi"},
    {"id": "z-transform", "mml": "<mo>&#437;</mo>", "latex": "\\mathcal{Z}"},
    {"id": "zeta", "mml": "<mi>&#950;</mi>", "latex": "\\zeta"}
  ];
}());
;/**
 * Created by panos on 10/29/15.
 */
(function () {
  'use strict';

  window.panels = [
    {
      id: "symbols",
      width: 334,
      visible: true,
      sections: [
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
            "square-superset-of", "square-superset-or-equal", "for-all",
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
      width: 205,
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
;/**
 * Created by panos on 11/3/15.
 */
(function () {
  'use strict';

  window.AreaPanel = function (panel, toolbar) {
    this.visible = panel.visible;
    this.id = panel.id;
    this.sectionWidth = panel.width;
    this.element = document.createElement("div");
    this.element.className = "fm-editor-panel-area";
    this.element.id = this.id + "-panel-area";
    this.toolbar = toolbar;
    this.sections = [];
    this.panel = panel;

    this.createPanelArea();
    this.createSections(panel.sections);
  };
  AreaPanel.__name__ = ["AreaPanel"];
  AreaPanel.prototype = {
    createPanelArea: function () {
      //Panel area
      var panelAreaObj = this;
      this.panelArea = document.createElement("div");
      this.panelArea.className = "fm-editor-panel-area-container";
      this.panelAreaBox = document.createElement("div");
      this.panelAreaBox.className = "fm-editor-panel-area-box";
      this.panelArea.appendChild(this.panelAreaBox);
      this.element.appendChild(this.panelArea);
      //Button area
      this.panelButtonArea = document.createElement("div");
      this.panelButtonArea.className = "fm-editor-panel-area-button-container";
      this.panelButtonArea.innerHTML =
        "<div class='fm-editor-panel-area-moveup-button'><div class='arrow-up'></div></div>" +
        "<div class='fm-editor-panel-area-movedown-button'><div class='arrow-down'></div></div>" +
        "<div class='fm-editor-panel-area-popup-button'><div class='left-down-arrow'></div></div>";
      this.element.appendChild(this.panelButtonArea);
      var popupButton = this.panelButtonArea.getElementsByClassName("fm-editor-panel-area-popup-button")[0];
      popupButton.addEventListener("click", function (event) {
        panelAreaObj.toggleSections(event);
      });
      var moveDownButton = this.panelButtonArea.getElementsByClassName("fm-editor-panel-area-movedown-button")[0];
      moveDownButton.addEventListener("click", function (event) {
        panelAreaObj.scrollDownAreaBox();
      });
      var moveUpButton = this.panelButtonArea.getElementsByClassName("fm-editor-panel-area-moveup-button")[0];
      moveUpButton.addEventListener("click", function (event) {
        panelAreaObj.scrollUpAreaBox();
      });
    },
    createSections: function (sections) {
      var sectionContainer = document.createElement("div");
      sectionContainer.className = "fm-editor-panel-area-section-container";
      var sectionBox = document.createElement("div");
      sectionBox.className = "fm-editor-panel-area-section-box";
      sectionBox.style.width = this.sectionWidth + "px";
      sectionContainer.appendChild(sectionBox);
      var sectionBoxContainer = document.createElement("div");
      sectionBoxContainer.className = "fm-editor-panel-area-section-box-container";
      sectionBox.appendChild(sectionBoxContainer);
      var sectionOverlapContainer = document.createElement("div");
      sectionOverlapContainer.className = "fm-editor-panel-area-section-overlap-container";
      sectionBoxContainer.appendChild(sectionOverlapContainer);
      var dropdownList = new DropdownList(this);
      sectionOverlapContainer.appendChild(dropdownList.element);
      for (var i = 0; i < sections.length; i++) {
        var section = sections[i];
        this.appendSection(section, sectionOverlapContainer, dropdownList, i == 0);
      }
      this.sectionsMountPoint = document.createElement("div");
      this.sectionsMountPoint.className = "fm-editor-panel-area-section-mount-point";
      this.sectionsMountPoint.appendChild(sectionContainer);
      this.element.appendChild(this.sectionsMountPoint);
    },
    toggleSections: function (event) {
      if (this.sectionsMountPoint != null) {
        if (this.active) {
          this.hideSections(event);
        } else {
          this.showSections(event);
        }
      }
      event.stopPropagation();
      return false;
    },
    showSections: function (event) {
      if (this.toolbar.activePanel != null) {
        this.toolbar.activePanel.hideSections(event);
      }
      this.sectionsMountPoint.style.display = "block";
      this.active = true;
      DomUtils.addClass(this.element, "active");
      this.toolbar.activePanel = this;
    },
    hideSections: function (event) {
      this.sectionsMountPoint.style.display = "none";
      this.active = false;
      DomUtils.removeClass(this.element, "active");
      this.toolbar.activePanel = null;
    },
    appendSection: function (section, sectionContainer, dropdownList, isActive) {
      var buttonSection = new ButtonSection(section, this);
      if (buttonSection.buttons.length > 0) {
        sectionContainer.appendChild(buttonSection.element);
        this.sections.push(buttonSection);
        dropdownList.addListItem(buttonSection, isActive);
      }
    },
    cloneButtonsToAreaBox: function (section) {
      this.panelAreaBox.innerHTML = "";
      var buttonsContainer = document.createElement("div");
      buttonsContainer.className = "fm-editor-section-buttons";
      this.panelAreaBox.appendChild(buttonsContainer);
      for (var i = 0; i < section.buttons.length; i++) {
        var sectionButton = section.buttons[i];
        var newButton = new Button(sectionButton.id, section);
        buttonsContainer.appendChild(newButton.element);
      }
      this.panelAreaBox.style.marginTop = "0px";
    },
    scrollUpAreaBox: function () {
      var marginTop = this.getMarginTop();
      this.panelAreaBox.style.marginTop = Math.min(0, marginTop + 56) + "px";
    },
    scrollDownAreaBox: function () {
      var height = parseInt(this.panelAreaBox.scrollHeight);
      var marginTop = this.getMarginTop();
      this.panelAreaBox.style.marginTop = Math.min(0, Math.max(-height + 56 + height % 28, marginTop - 56)) + "px";
    },
    getMarginTop: function () {
      var marginTop = this.panelAreaBox.style.marginTop;
      if (marginTop.length == 0) marginTop = 0;
      return parseInt(marginTop);
    },
    redraw: function () {
      this.sections = [];
      this.panelArea.parentNode.removeChild(this.panelArea);
      this.panelButtonArea.parentNode.removeChild(this.panelButtonArea);
      this.sectionsMountPoint.parentNode.removeChild(this.sectionsMountPoint);
      this.createPanelArea();
      this.createSections(this.panel.sections);
    },
    element: null,
    panelArea: null,
    panelButtonArea: null,
    panelAreaBox: null,
    sectionsMountPoint: null,
    sectionWidth: null,
    visible: false,
    active: false,
    sections: null,
    panel: null,
    id: null,
    toolbar: null,
    __class__: AreaPanel
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.Button = function (button, section) {
    this.id = button;
    this.element = document.createElement("span");
    this.element.className = "fm-editor-button math math-" + button;
    this.element.id = this.id + "-btn";
    this.section = section;
    this.createEvent();
    var button = this;
    this.element.addEventListener("click", function (event) {
      button.addEquationEvent.clientX = event.clientX;
      button.addEquationEvent.clientY = event.clientY;
      button.element.dispatchEvent(button.addEquationEvent);
    });
  };
  Button.__name__ = ["Button"];
  Button.prototype = {
    createEvent: function () {
      this.addEquationEvent = document.createEvent('Event');
      this.addEquationEvent.formulaAction = this.id;
      this.addEquationEvent.initEvent('addEquation', true, true);
    },
    element: null,
    addEquationEvent: null,
    id: null,
    section: null,
    __class__: Button
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.ButtonPanel = function (panel, toolbar) {
    this.visible = panel.visible;
    this.id = panel.id;
    this.name = trans[panel.id] || panel.id;
    this.sectionWidth = panel.width;
    this.element = document.createElement("div");
    this.element.className = "fm-editor-panel";
    this.element.id = this.id + "-panel";
    this.toolbar = toolbar;
    this.panel = panel;
    this.sections = [];
    this.createPanelButton();
    this.createSections(panel.sections);
  };
  ButtonPanel.__name__ = ["ButtonPanel"];
  ButtonPanel.prototype = {
    createPanelButton: function () {
      this.buttonElement = document.createElement("div");
      this.buttonElement.className = "fm-editor-panel-button";
      var iconDiv = document.createElement("div");
      iconDiv.className = "fm-editor-panel-button-icon math math-" + this.id + "-btn";
      var labelDiv = document.createElement("div");
      labelDiv.className = "fm-editor-panel-button-label";
      labelDiv.innerHTML = this.name;
      var arrowDiv = document.createElement("div");
      arrowDiv.className = "arrow-down";
      this.buttonElement.appendChild(iconDiv);
      this.buttonElement.appendChild(labelDiv);
      this.buttonElement.appendChild(arrowDiv);
      this.element.appendChild(this.buttonElement);
      var buttonPanel = this;
      this.buttonElement.addEventListener("click", function (event) {
        buttonPanel.toggleSections(event);
      });
    },
    createSections: function (sections) {
      var sectionDiv = document.createElement("div");
      sectionDiv.className = "fm-editor-section-container";
      sectionDiv.style.width = this.sectionWidth + "px";
      for (var i = 0; i < sections.length; i++) {
        var section = sections[i];
        this.appendSection(section, sectionDiv);
      }
      this.sectionsMountPoint = document.createElement("div");
      this.sectionsMountPoint.className = "fm-editor-section-mount-point";
      this.sectionsMountPoint.appendChild(sectionDiv);
      this.element.appendChild(this.sectionsMountPoint);
    },
    toggleSections: function (event) {
      if (this.sectionsMountPoint != null) {
        if (this.active) {
          this.hideSections(event);
        } else {
          this.showSections(event);
        }
      }
      this.toolbar.mlangPanel.hideList();
      event.stopPropagation();
      return false;
    },
    showSections: function (event) {
      if (this.toolbar.activePanel != null) {
        this.toolbar.activePanel.hideSections(event);
      }
      this.active = true;
      DomUtils.addClass(this.element, "active");
      var finalWidth = this.buttonElement.getBoundingClientRect().left + this.sectionWidth;
      if (finalWidth > document.body.clientWidth) {
        this.sectionsMountPoint.style.left = -this.sectionWidth + this.buttonElement.clientWidth + "px";
      } else {
        this.sectionsMountPoint.style.left = "-1px";
      }
      this.toolbar.activePanel = this;
    },
    hideSections: function (event) {
      this.active = false;
      DomUtils.removeClass(this.element, "active");
      this.toolbar.activePanel = null;
    },
    appendSection: function (section, sectionDiv) {
      var buttonSection = new ButtonSection(section, this);
      if (buttonSection.buttons.length > 0) {
        sectionDiv.appendChild(buttonSection.element);
        this.sections.push(buttonSection);
      }
    },
    redraw: function () {
      this.sections = [];
      this.sectionsMountPoint.parentNode.removeChild(this.sectionsMountPoint);
      this.createSections(this.panel.sections);
    },
    element: null,
    sectionsMountPoint: null,
    buttonElement: null,
    sectionWidth: null,
    visible: false,
    active: false,
    sections: null,
    panel: null,
    id: null,
    name: null,
    toolbar: null,
    __class__: ButtonPanel
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.ButtonSection = function (section, panel) {
    this.id = section.id;
    this.name = section.name || trans[section.id] || section.id;
    this.element = document.createElement("div");
    this.element.className = "fm-editor-section";
    this.element.id = this.id + "-section";
    this.panel = panel;
    this.createTitle();
    this.buttons = [];
    if (section.children && section.children.length > 0) {
      this.createButtons(section.children);
    }
  };
  ButtonSection.__name__ = ["ButtonSection"];
  ButtonSection.prototype = {
    createButtons: function (buttons) {
      var actionsHash = this.panel.toolbar.editor.actions;
      var mlang = this.panel.toolbar.editor.mlang;
      var buttonsElt = document.createElement("div");
      buttonsElt.className = "fm-editor-section-buttons";
      for (var i = 0; i < buttons.length; i++) {
        var child = buttons[i];
        var action = actionsHash.get(child);
        if (!!action && !!action[mlang]) {
          var button = new Button(child, this);
          buttonsElt.appendChild(button.element);
          this.buttons.push(button);
        }
      }
      this.element.appendChild(buttonsElt);
    },
    createTitle: function () {
      var title = document.createElement("div");
      title.className = "fm-editor-section-title";
      title.innerHTML = this.name;
      this.element.appendChild(title);
    },
    setActive: function () {
      if (!this.active) {
        DomUtils.addClass(this.element, "active");
        this.active = true;
      }
    },
    unsetActive: function () {
      if (this.active) {
        DomUtils.removeClass(this.element, "active");
        this.active = false;
      }
    },
    element: null,
    active: false,
    buttons: null,
    id: null,
    name: null,
    panel: null,
    __class__: ButtonSection
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.DropdownList = function (areaPanel) {
    this.areaPanel = areaPanel;
    this.element = document.createElement("div");
    this.element.className = "fm-editor-dropdown fm-editor-dropdown-button";
    this.element.id = areaPanel.id + "-dropdown";
    this.createLabel();
    this.createList();
  };
  DropdownList.__name__ = ["DropdownList"];
  DropdownList.prototype = {
    createLabel: function () {
      this.labelButton = document.createElement("div");
      this.labelButton.className = "fm-editor-dropdown-label-button";
      this.labelButtonText = document.createElement("div");
      this.labelButtonText.className = "fm-editor-dropdown-label-button-text";
      this.labelButton.appendChild(this.labelButtonText);
      var arrowDown = document.createElement("div");
      arrowDown.className = "arrow-down";
      this.labelButton.appendChild(arrowDown);
      this.element.appendChild(this.labelButton);
      var dropdownList = this;
      this.labelButton.addEventListener("click", function (event) {
        dropdownList.toggleList(event)
      });
    },
    createList: function () {
      this.listMountPoint = document.createElement("div");
      this.listMountPoint.className = "fm-editor-dropdown-list-mount-point";
      var listElement = document.createElement("div");
      listElement.className = "fm-editor-dropdown-list-items";
      this.listItemContainer = document.createElement("div");
      this.listItemContainer.className = "fm-editor-dropdown-list-items-container";
      listElement.appendChild(this.listItemContainer);
      this.listMountPoint.appendChild(listElement);
      this.element.appendChild(this.listMountPoint);
    },
    toggleList: function (event) {
      if (this.active) {
        this.hideList(event);
      } else {
        this.showList(event);
      }
      event.stopPropagation();
      return false;
    },
    hideList: function (event) {
      DomUtils.removeClass(this.listMountPoint, "active");
      this.active = false;
    },
    showList: function (event) {
      DomUtils.addClass(this.listMountPoint, "active");
      this.active = true;
    },
    addListItem: function (section, setActive) {
      var item = new DropdownListItem(section, this);
      this.listItemContainer.appendChild(item.element);
      if (setActive) {
        item.setActive();
      }
    },
    changeActiveItem: function (item) {
      if (this.activeItem !== null) {
        this.activeItem.unsetActive();
      }
      this.activeItem = item;
      this.labelButtonText.innerHTML = item.name;
      this.areaPanel.cloneButtonsToAreaBox(this.activeItem.section);
    },
    element: null,
    active: false,
    labelButton: null,
    labelButtonText: null,
    listMountPoint: null,
    listItemContainer: null,
    activeItem: null,
    areaPanel: null,
    __class__: DropdownList
  };
}());
;/**
 * Created by panos on 11/5/15.
 */
(function () {
  'use strict';

  window.DropdownListItem = function (section, dropdownList) {
    this.id = section.id;
    this.name = section.name;
    this.element = document.createElement("div");
    this.element.className = "fm-editor-dropdown-list-item";
    this.element.id = this.id + "-list-item";
    this.section = section;
    this.dropdownList = dropdownList;
    this.element.innerHTML = this.name;
    this.addListener();
  };
  DropdownListItem.__name__ = ["DropdownListItem"];
  DropdownListItem.prototype = {
    addListener: function () {
      var item = this;
      this.element.addEventListener("click", function (event) {
        item.changeActive(event);
      });
    },
    changeActive: function (event) {
      this.setActive();
      this.dropdownList.hideList(event);
      event.stopPropagation();
      return false;
    },
    setActive: function () {
      this.dropdownList.changeActiveItem(this);
      this.section.setActive();
      if (!this.active) {
        DomUtils.addClass(this.element, "active");
        this.active = true;
      }
    },
    unsetActive: function () {
      this.section.unsetActive();
      if (this.active) {
        DomUtils.removeClass(this.element, "active");
        this.active = false;
      }
    },
    element: null,
    active: false,
    id: null,
    name: null,
    section: null,
    dropdownList: null,
    __class__: DropdownListItem
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.Editor = function (actions, panels, parameters) {
    this.lang = parameters.lang || this.lang;
    this.lang = this.lang.replace(/_[a-zA-Z]+/g, "").toLocaleLowerCase();
    this.mlang = parameters.mlang || this.mlang;
    this.initEquation = parameters.equation || this.initEquation;
    this.buildActionsHash(actions);
    this.panels = panels;
    var editor = this;
    DomUtils.loadJsFile("js/translations/" + this.lang + ".js", function () {
      editor.init()
    }, "js/translations/fr.js");
  };
  Editor.__name__ = ["Editor"];
  Editor.prototype = {
    init: function () {
      this.element = document.createElement("div");
      this.element.className = "fm-editor";
      this.createToolbar(this.panels);
      this.createTextArea();
      this.createResultArea();
      document.getElementById("fm-editor-body").appendChild(this.element);
      this.createMatixActionPopup();
      var editor = this;
      this.element.addEventListener("addEquation", function (event) {
        editor.addEquation(event);
      });
      document.addEventListener("click", function (e) {
        if (editor.toolbar.activePanel != null) {
          editor.toolbar.activePanel.hideSections(e);
        }
        editor.toolbar.mlangPanel.hideList();
        if (e.target.id.indexOf("btn") == -1 || (e.target.id.indexOf("table") == -1 && e.target.id.indexOf("matrix") == -1)) {
          editor.hideMatrixPopup();
        }
      });
      if (this.initEquation !== null) {
        this.insertEquationToTextarea(decodeURIComponent(this.initEquation));
      }
    },
    createToolbar: function (panels) {
      this.toolbar = new Toolbar(panels, this);
      this.element.appendChild(this.toolbar.element);
    },
    addEquation: function (event) {
      var actionHash = this.actions.get(event.formulaAction);
      var equation = actionHash[this.mlang];
      if (actionHash.matrix) {
        this.currentMatrixEquation = equation;
        this.showMatrixPopup(event.clientX, event.clientY);
      } else {
        var regex = /\{\{\$\}\}/g;
        var matches = equation.match(regex);
        if (matches && matches.length > 0) {
          if (matches == 1) {
            equation = equation.replace(regex, "x");
          } else {
            var cnt = 1;
            equation = equation.replace(regex, function () {
              return "x" + (cnt++);
            });
          }
        }
        equation = equation.trim();
        this.insertEquationToTextarea(equation);
      }
    },
    insertEquationToTextarea: function (equation) {
      if (this.mlang == "latex") {
        equation = equation + " ";
      }
      this.textarea.insertAtCaret(equation);
      this.renderEquationToResultarea(this.textarea.value);
    },
    insertMatrixToTextarea: function () {
      var rows = parseInt(document.getElementById("fm-editor-matrix-rows").value);
      var columns = parseInt(document.getElementById("fm-editor-matrix-columns").value);
      var equation = this.currentMatrixEquation;
      this.hideMatrixPopup();
      if (equation !== null && rows > 0 && columns > 0) {
        var matrixCode = "";
        if (this.mlang == "latex") {
          matrixCode = this.createMatrixCodeLatex(rows, columns);
        } else {
          matrixCode = this.createMatrixCodeMml(rows, columns);
        }
        equation = equation.replace(/\{\{\$\}\}/g, matrixCode);
        equation = equation.trim();
        this.insertEquationToTextarea(equation);
      }
    },
    createMatrixCodeLatex: function (rows, columns) {
      var matrixCode = "";
      for (var j = 0; j < rows; j++) {
        for (var i = 0; i < columns; i++) {
          matrixCode += " x_" + (i + 1 + j * columns);
          if (i < (columns - 1)) {
            matrixCode += " &";
          } else {
            matrixCode += " ";
          }
        }
        if (j < (rows - 1)) {
          matrixCode += "\\\\";
        }
      }
      return matrixCode;
    },
    createMatrixCodeMml: function (rows, columns) {
      var matrixCode = "";
      for (var j = 0; j < rows; j++) {
        matrixCode += "<mtr>";
        for (var i = 0; i < columns; i++) {
          matrixCode += "<mtd><msub><mi>x</mi><mi>" + (i + 1 + j * columns) + "</mi></msub></mtd>";
        }
        matrixCode += "</mtr>";
      }
      return matrixCode;
    },
    renderEquationToResultarea: function (equation) {
      equation = equation || "";
      if (equation.trim() !== "") {
        if (this.mlang == "latex") {
          equation = "$$" + equation.trim() + "$$";
        } else {
          equation = "<math xmlns=\"http://www.w3.org/1998/Math/MathML\" mode=\"display\">" + equation.trim() + "</math>";
        }
        this.toolbar.mlangPanel.disable();
        this.resultarea.innerHTML = equation;
        MathJax.Hub.Queue(["Typeset", MathJax.Hub, this.resultarea]);
      } else {
        this.toolbar.mlangPanel.enable();
        this.resultarea.innerHTML = "";
      }
    },
    createTextArea: function () {
      var textAreaContainer = document.createElement("div");
      textAreaContainer.className = "fm-editor-content-area";
      this.textarea = document.createElement("textarea");
      this.textarea.className = "fm-editor-content";
      textAreaContainer.appendChild(this.textarea);
      this.element.appendChild(textAreaContainer);
      var editor = this;
      this.textarea.addEventListener('input', function () {
        editor.renderEquationToResultarea(this.value);
      });
    },
    createResultArea: function () {
      var resultAreaContainer = document.createElement("div");
      resultAreaContainer.className = "fm-editor-result-area";
      var resultAreaLabel = document.createElement("div");
      resultAreaLabel.className = "fm-editor-result-area-label";
      resultAreaLabel.innerHTML = trans["result"] || "result";
      this.resultarea = document.createElement("div");
      this.resultarea.className = "fm-editor-result-area-inner";
      resultAreaContainer.appendChild(resultAreaLabel);
      resultAreaContainer.appendChild(this.resultarea);
      this.element.appendChild(resultAreaContainer);
    },
    createMatixActionPopup: function () {
      this.matrixPopupMount = document.createElement("div");
      this.matrixPopupMount.className = "fm-editor-matrix-popup-mount";
      var matrixPopupContainer = document.createElement("div");
      matrixPopupContainer.className = "fm-editor-matrix-popup-container";
      matrixPopupContainer.innerHTML = "<table>" +
        "<tr><td>" + (trans["rows"] || "rows") + ":</td><td><input id=\"fm-editor-matrix-rows\" type='number' name='matrix-rows'/></td></tr>" +
        "<tr><td>" + (trans["columns"] || "columns") + ":</td><td><input id=\"fm-editor-matrix-columns\" type='number' name='matrix-columns'/></td></tr>" +
        "<tr><td colspan='2'><button type='button' id='fm-editor-matrix-create-btn'>" + (trans["ok"] || "ok") + "</button></td></tr>" +
        "</table>";
      this.matrixPopupMount.appendChild(matrixPopupContainer);
      this.element.appendChild(this.matrixPopupMount);
      //Adding listeners
      var editor = this;
      this.matrixPopupMount.addEventListener("click", function (event) {
        event.stopPropagation();
        return false;
      });
      document.getElementById("fm-editor-matrix-create-btn").addEventListener("click", function () {
        editor.insertMatrixToTextarea();
      });
      document.getElementById("fm-editor-matrix-rows").addEventListener("keypress", function (event) {
        if (event.which == 13 || event.keyCode == 13) {
          document.getElementById("fm-editor-matrix-columns").focus();
        }
      });
      document.getElementById("fm-editor-matrix-columns").addEventListener("keypress", function (event) {
        if (event.which == 13 || event.keyCode == 13) {
          editor.insertMatrixToTextarea();
        }
      });
    },
    showMatrixPopup: function (x, y) {
      var rowsInput = document.getElementById("fm-editor-matrix-rows");
      rowsInput.value = 2;
      document.getElementById("fm-editor-matrix-columns").value = 2;
      DomUtils.addClass(this.matrixPopupMount, "active");
      this.matrixPopupMount.style.top = y - 40 + "px";
      this.matrixPopupMount.style.left = x - 20 + "px";
      rowsInput.focus();
    },
    hideMatrixPopup: function () {
      DomUtils.removeClass(this.matrixPopupMount, "active");
      this.currentMatrixEquation = null;
    },
    buildActionsHash: function (actions) {
      this.actions = new HashArray();
      for (var i = 0; i < actions.length; i++) {
        var action = actions[i];
        this.actions.set(action.id, action);
      }
    },
    getEquationPng: function (callback) {
      var svg = this.resultarea.getElementsByTagName("svg")[0];
      if (svg) {
        svg = svg.cloneNode(true);
        var editor = this;
        DomUtils.replaceSVGUseWithGraphElements(svg);
        var image = new Image();
        image.onload = function () {
          var canvas = document.createElement("canvas");
          canvas.width = image.width;
          canvas.height = image.height;
          var context = canvas.getContext("2d");
          context.drawImage(image, 0, 0);
          var imgSrc = canvas.toDataURL('image/png');
          var mlang = editor.mlang;
          var equation = editor.textarea.value.trim();
          callback(imgSrc, mlang, equation);
        };
        var svgAsXml = (new XMLSerializer).serializeToString(svg);
        image.src = 'data:image/svg+xml,' + encodeURIComponent(svgAsXml);
      } else {
        callback(null, null, null);
      }
    },
    panels: null,
    element: null,
    toolbar: null,
    actions: null,
    textarea: null,
    resultarea: null,
    currentMatrixEquation: null,
    matrixPopupMount: null,
    lang: "en",
    mlang: "latex",
    initEquation: null,
    __class__: Editor
  };
}());
;/**
 * Created by panos on 11/12/15.
 */
(function () {
  'use strict';

  window.MlangPanel = function (toolbar) {
    this.id = "mlang";
    this.element = document.createElement("div");
    this.element.className = "fm-editor-panel";
    this.toolbar = toolbar;
    this.currentMlang = this.toolbar.editor.mlang;
    this.createPanelButton();
    this.createList();
  };
  MlangPanel.__name__ = ["MlangPanel"];
  MlangPanel.prototype = {
    createPanelButton: function () {
      var buttonDiv = document.createElement("div");
      buttonDiv.className = "fm-editor-panel-button";
      var iconDiv = document.createElement("div");
      iconDiv.className = "fm-editor-panel-button-icon math math-" + this.id + "-btn";
      this.labelButtonText = document.createElement("div");
      this.labelButtonText.className = "fm-editor-panel-button-label";
      this.labelButtonText.innerHTML = this.mlangs[this.currentMlang];
      var arrowDiv = document.createElement("div");
      arrowDiv.className = "arrow-down";
      buttonDiv.appendChild(iconDiv);
      buttonDiv.appendChild(this.labelButtonText);
      buttonDiv.appendChild(arrowDiv);
      this.element.appendChild(buttonDiv);
      var mlangPanel = this;
      buttonDiv.addEventListener("click", function (event) {
        if (mlangPanel.enabled) mlangPanel.toggleList(event);
        if (mlangPanel.toolbar.activePanel != null) {
          mlangPanel.toolbar.activePanel.hideSections(event);
        }
        event.stopPropagation();
        return false;
      });
    },
    createList: function () {
      this.listMountPoint = document.createElement("div");
      this.listMountPoint.className = "fm-editor-dropdown-list-mount-point";
      var listElement = document.createElement("div");
      listElement.className = "fm-editor-dropdown-list-items";
      this.listItemContainer = document.createElement("div");
      this.listItemContainer.className = "fm-editor-dropdown-list-items-container";
      // Create latex and mml dropdown items
      var latexSection = new ButtonSection({id: "latex", name: "LaTeX", children: []}, this);
      var latexItem = new DropdownListItem(latexSection, this);
      this.listItemContainer.appendChild(latexItem.element);
      var mmlSection = new ButtonSection({id: "mml", name: "MathML", children: []}, this);
      var mmlItem = new DropdownListItem(mmlSection, this);
      this.listItemContainer.appendChild(mmlItem.element);
      if (this.currentMlang == "latex") {
        latexItem.setActive();
      } else {
        mmlItem.setActive();
      }
      listElement.appendChild(this.listItemContainer);
      this.listMountPoint.appendChild(listElement);
      this.element.appendChild(this.listMountPoint);
    },
    toggleList: function (event) {
      if (this.active) {
        this.hideList();
      } else {
        this.showList();
      }
    },
    showList: function () {
      DomUtils.addClass(this.listMountPoint, "active");
      this.active = true;
    },
    hideList: function () {
      DomUtils.removeClass(this.listMountPoint, "active");
      this.active = false;
    },
    changeActiveItem: function (item) {
      if (this.activeItem != null) {
        this.activeItem.unsetActive();
        this.toolbar.editor.mlang = item.id;
        this.toolbar.redraw();
      }
      this.activeItem = item;
      this.labelButtonText.innerHTML = item.name;
    },
    disable: function () {
      if (this.enabled) {
        this.enabled = false;
        DomUtils.addClass(this.element, "disabled");
      }
    },
    enable: function () {
      if (!this.enabled) {
        this.enabled = true;
        DomUtils.removeClass(this.element, "disabled");
      }
    },
    enabled: true,
    active: false,
    activeItem: null,
    element: null,
    labelButtonText: null,
    listItemContainer: null,
    listMountPoint: null,
    currentMlang: null,
    toolbar: null,
    mlangs: {"latex": "LaTeX", "mml": "MathML"},
    __class__: MlangPanel
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.Toolbar = function (panels, editor) {
    this.element = document.createElement("div");
    this.element.className = "fm-editor-toolbar";
    this.editor = editor;
    this.createPanels(panels);
  };
  Toolbar.__name__ = ["Toolbar"];
  Toolbar.prototype = {
    createPanels: function (panels) {
      this.panels = [];
      this.activePanel = null;
      var panelDiv = document.createElement("div");
      panelDiv.className = "fm-editor-panel-container";
      this.createMlangPanel(panelDiv);
      for (var i = 0; i < panels.length; i++) {
        var panel = panels[i];
        if (panel.visible) {
          var panelObj = new AreaPanel(panel, this);
        } else {
          var panelObj = new ButtonPanel(panel, this);
        }
        panelDiv.appendChild(panelObj.element);
        this.panels.push(panelObj);
      }
      this.element.appendChild(panelDiv);
    },
    createMlangPanel: function (parentDiv) {
      this.mlangPanel = new MlangPanel(this);
      parentDiv.appendChild(this.mlangPanel.element);
    },
    redraw: function () {
      for (var i = 0; i < this.panels.length; i++) {
        var panel = this.panels[i];
        panel.redraw();
      }
    },
    element: null,
    activePanel: null,
    mlangPanel: null,
    editor: null,
    panels: null,
    __class__: Toolbar
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.ArrayIterator = function (a) {
    this.arr = a;
  };
  ArrayIterator.__name__ = ["ArrayIterator"];
  ArrayIterator.prototype = {
    hasNext: function () {
      return this.cur < this.arr.length;
    },
    next: function () {
      return this.arr[this.cur++];
    },
    cur: 0,
    arr: [],
    __class__: ArrayIterator
  };
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.HashArray = function () {
    this.obj = {};
  };
  HashArray.__name__ = ["HashArray"];
  HashArray.prototype = {
    toString: function () {
      var str = "";
      str += "{";
      var it = this.keys();
      while (it.hasNext()) {
        var i = it.next();
        str += i;
        str += " => ";
        str += this.get(i);
        if (it.hasNext()) str += ", ";
      }
      ;
      str += "}";
      return str;
    },
    iterator: function () {
      return {
        ref: this.obj,
        it: this.keys(),
        hasNext: function () {
          return this.it.hasNext();
        },
        next: function () {
          var i = this.it.next();
          return this.ref["$" + i];
        }
      };
    },
    keys: function () {
      var a = [];
      for (var vParameter in this.obj) {
        if (this.obj.hasOwnProperty(vParameter)) a.push(vParameter.substr(1));
      }
      ;
      return ArrayIterator(a);
    },
    remove: function (vParameter) {
      vParameter = "$" + vParameter;
      if (!this.obj.hasOwnProperty(vParameter)) return false;
      delete(this.obj[vParameter]);
      return true;
    },
    exists: function (vParameter) {
      return this.obj.hasOwnProperty("$" + vParameter);
    },
    get: function (vParameter) {
      return this.obj["$" + vParameter];
    },
    set: function (vParameter, value) {
      this.obj["$" + vParameter] = value;
    },
    obj: null,
    __class__: HashArray
  }
}());
;/**
 * Created by panos on 10/16/15.
 */
(function () {
  'use strict';

  window.DomUtils = {
    hasClass: function (el, className) {
      if (el.classList)
        return el.classList.contains(className);
      else
        return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
    },
    addClass: function (el, className) {
      if (el.classList)
        el.classList.add(className);
      else if (!this.hasClass(el, className)) el.className += " " + className;
    },
    removeClass: function (el, className) {
      if (el.classList)
        el.classList.remove(className)
      else if (this.hasClass(el, className)) {
        var reg = new RegExp('(\\s|^)' + className + '(\\s|$)')
        el.className = el.className.replace(reg, ' ')
      }
    },
    loadJsFile: function (filename, onLoadCallback, defaultFilename) {
      var fileref = document.createElement('script');
      if (defaultFilename) {
        fileref.onerror = function () {
          DomUtils.loadJsFile(defaultFilename, onLoadCallback);
        };
      }
      fileref.onload = onLoadCallback;
      fileref.type = "text/javascript";
      fileref.src = filename;
      document.getElementsByTagName("head")[0].appendChild(fileref);
    },
    replaceSVGUseWithGraphElements: function (svg) {
      var useElements = svg.getElementsByTagName("use");
      var originalElements = [];
      var newUseElements = [];
      // Get all use elements
      for (var i = 0; i < useElements.length; i++) {
        var useElement = useElements[i];
        var originalElementId = useElement.getAttribute("href").replace("#", "");
        var originalElement = document.getElementById(originalElementId).cloneNode(true);
        originalElement.id += "-c-" + (new Date()).getTime();
        var position = {};
        //For every element get all attributes and copy them to graph element
        for (var j = 0; j < useElement.attributes.length; j++) {
          var attribute = useElement.attributes[j];
          if (attribute.nodeName !== "href" && attribute.nodeName !== "x" && attribute.nodeName !== "y") {
            originalElement.setAttribute(attribute.nodeName, attribute.nodeValue);
          } else if (attribute.nodeName == "x" || attribute.nodeName == "y") {
            //If position attributes (x or y) are set, create a position element
            position[attribute.nodeName] = attribute.nodeValue;
          }
        }
        //If position element is set then add or change tranform attribute
        if (position.x) {
          var positionStr = (position.x || 0) + ", " + (position.y || 0);
          var transform = originalElement.getAttribute("transform") || "";
          if (transform !== "") transform += " ";
          transform += "translate(" + positionStr + ")";
          originalElement.setAttribute("transform", transform);
        }
        originalElements.push(originalElement);
        newUseElements.push(useElement);
      }
      for (var i = 0; i < originalElements.length; i++) {
        var tmp = newUseElements[i];
        newUseElements[i] = i;
        tmp.parentNode.replaceChild(originalElements[i], tmp);
      }
    }
  };

  HTMLTextAreaElement.prototype.insertAtCaret = function (text) {
    text = text || '';
    if (document.selection) {
      // IE
      this.focus();
      var sel = document.selection.createRange();
      sel.text = text;
    } else if (this.selectionStart || this.selectionStart === 0) {
      // Others
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      this.value = this.value.substring(0, startPos) +
        text +
        this.value.substring(endPos, this.value.length);
      this.selectionStart = startPos + text.length;
      this.selectionEnd = startPos + text.length;
    } else {
      this.value += text;
    }
  };

  HTMLTextAreaElement.prototype.getCaret = function () {
    if (this.selectionStart) {
      return this.selectionStart;
    } else if (document.selection) {
      this.focus();

      var r = document.selection.createRange();
      if (r == null) {
        return 0;
      }

      var re = this.createTextRange(),
        rc = re.duplicate();
      re.moveToBookmark(r.getBookmark());
      rc.setEndPoint('EndToStart', re);

      return rc.text.length;
    }
    return 0;
  }

  window.Url = {
    get get() {
      var vars = {};
      if (window.location.search.length !== 0)
        window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
          key = decodeURIComponent(key);
          if (typeof vars[key] === "undefined") {
            vars[key] = decodeURIComponent(value);
          }
          else {
            vars[key] = [].concat(vars[key], decodeURIComponent(value));
          }
        });
      return vars;
    }
  }
}());