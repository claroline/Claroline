
// everything that is {{$}} is considered as variable to be filled by the user
export const actions = [
  {'id': 'alef', 'mml': '<mo>&#8501;</mo>', 'latex': '\\aleph'},
  {
    'id': 'aligned-equations',
    'mml': '<mtable columnspacing="2px" columnalign="right center left"><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mo>=</mo></mtd><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mo>=</mo></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable>',
    'latex': '\\begin{align*} & {{$}} = {{$}} \\\\ & {{$}} = {{$}} \\end{align*}'
  },
  {'id': 'almost-equal', 'mml': '<mo>&#8776;</mo>', 'latex': '\\approx'},
  {'id': 'alpha', 'mml': '<mi>&#945;</mi>', 'latex': '\\alpha'},
  {'id': 'angle', 'mml': '<mo>&#8736;</mo>', 'latex': '\\angle'},
  {
    'id': 'angle-brackets',
    'mml': '<mfenced open="&lt;" close="&gt;"><mi>{{$}}</mi></mfenced>',
    'latex': '\\langle {{$}} \\rangle'
  },
  {
    'id': 'angle-brackets-bar',
    'mml': '<mfenced open="&lt;" close="&gt;" separators="|"><mi>{{$}}</mi><mi>{{$}}</mi></mfenced>',
    'latex': '\\langle {{$}} \\mid {{$}} \\rangle'
  },
  {'id': 'aproximately-equal', 'mml': '<mo>&#8773;</mo>', 'latex': '\\cong'},
  {
    'id': 'arccosine',
    'mml': '<mi>arccos</mi><mfenced><mrow><mi>{{$}}</mi></mrow></mfenced>',
    'latex': '\\arccos({{$}})'
  },
  {
    'id': 'arcsine',
    'mml': '<mi>arcsin</mi><mfenced><mrow><mi>{{$}}</mi></mrow></mfenced>',
    'latex': '\\arcsin({{$}})'
  },
  {
    'id': 'arctangent',
    'mml': '<mi>arctan</mi><mfenced><mrow><mi>{{$}}</mi></mrow></mfenced>',
    'latex': '\\arctan({{$}})'
  },
  {'id': 'arrow-accent', 'mml': '<mover><mi>{{$}}</mi><mo>&#8594;</mo></mover>', 'latex': '\\vec{{{$}}}'},
  {'id': 'asterisk', 'mml': '<mo>*</mo>', 'latex': '\\ast'},
  {'id': 'asymptotically-equal', 'mml': '<mo>&#8771;</mo>', 'latex': '\\simeq'},
  {'id': 'back-space', 'mml': '<mspace width="-0.2em"/>', 'latex': '\\!'},
  {'id': 'bar-accent', 'mml': '<mover><mi>{{$}}</mi><mo>&#175;</mo></mover>', 'latex': '\\bar{{{$}}}'},
  {'id': 'because', 'mml': '<mo>&#8757;</mo>', 'latex': '\\because'},
  {'id': 'beta', 'mml': '<mi>&#946;</mi>', 'latex': '\\beta'},
  {'id': 'bevelled-fraction', 'mml': '<mfrac bevelled="true"><mi>{{$}}</mi><mi>{{$}}</mi></mfrac>', 'latex': ''},
  {
    'id': 'bevelled-small-fraction',
    'mml': '<mstyle displaystyle="false"><mfrac bevelled="true"><mi>{{$}}</mi><mi>{{$}}</mi></mfrac></mstyle>',
    'latex': ''
  },
  {'id': 'big-intersection', 'mml': '<mo largeop="true">&#8745;</mo>', 'latex': '\\bigcap'},
  {'id': 'big-operator-subscript', 'mml': '<msub><mo largeop="true">{{$}}</mo><mi>{{$}}</mi></msub>', 'latex': ''},
  {
    'id': 'big-operator-subsuperscript',
    'mml': '<msubsup><mo largeop="true">{{$}}</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>',
    'latex': ''
  },
  {
    'id': 'big-operator-underoverscript',
    'mml': '<munderover><mo largeop="true">{{$}}</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': '{{$}}_{{{$}}}^{{{$}}}'
  },
  {
    'id': 'big-operator-underscript',
    'mml': '<munder><mo largeop="true">{{$}}</mo><mi>{{$}}</mi></munder>',
    'latex': '{{$}}_{{{$}}}'
  },
  {'id': 'big-square-cap', 'mml': '<mo largeop="true">&#8851;</mo>', 'latex': ''},
  {'id': 'big-square-cup', 'mml': '<mo largeop="true">&#8852;</mo>', 'latex': '\\bigsqcup'},
  {'id': 'big-union', 'mml': '<mo largeop="true">&#8746;</mo>', 'latex': '\\bigcup'},
  {
    'id': 'bottom-curly-bracket',
    'mml': '<munder><mrow><mi>{{$}}</mi></mrow><mo>&#9183;</mo></munder>',
    'latex': '\\underbrace{{{$}}}'
  },
  {'id': 'bottom-parenthesis', 'mml': '<munder><mrow><mi>{{$}}</mi></mrow><mo>&#9181;</mo></munder>', 'latex': ''},
  {'id': 'bullet', 'mml': '<mo>&#8729;</mo>', 'latex': '\\bullet'},
  {'id': 'capital-alpha', 'mml': '<mi>&#913;</mi>', 'latex': 'A'},
  {'id': 'capital-beta', 'mml': '<mi>&#914;</mi>', 'latex': 'B'},
  {'id': 'capital-chi', 'mml': '<mi>&#935;</mi>', 'latex': 'X'},
  {'id': 'capital-delta', 'mml': '<mi>&#916;</mi>', 'latex': '\\Delta'},
  {'id': 'capital-epsilon', 'mml': '<mi>&#917;</mi>', 'latex': 'E'},
  {'id': 'capital-eta', 'mml': '<mi>&#919;</mi>', 'latex': 'H'},
  {'id': 'capital-gamma', 'mml': '<mi>&#915;</mi>', 'latex': '\\Gamma'},
  {'id': 'capital-iota', 'mml': '<mi>&#921;</mi>', 'latex': 'I'},
  {'id': 'capital-kappa', 'mml': '<mi>&#922;</mi>', 'latex': 'K'},
  {'id': 'capital-lambda', 'mml': '<mi>&#923;</mi>', 'latex': '\\Lambda'},
  {'id': 'capital-mi', 'mml': '<mi>&#924;</mi>', 'latex': 'M'},
  {'id': 'capital-ni', 'mml': '<mi>&#925;</mi>', 'latex': 'N'},
  {'id': 'capital-omega', 'mml': '<mi>&#937;</mi>', 'latex': '\\Omega'},
  {'id': 'capital-omicron', 'mml': '<mi>&#927;</mi>', 'latex': 'O'},
  {'id': 'capital-phi', 'mml': '<mi>&#934;</mi>', 'latex': '\\Phi'},
  {'id': 'capital-pi', 'mml': '<mi>&#928;</mi>', 'latex': '\\Pi'},
  {'id': 'capital-psi', 'mml': '<mi>&#936;</mi>', 'latex': '\\Psi'},
  {'id': 'capital-rho', 'mml': '<mi>&#929;</mi>', 'latex': 'P'},
  {'id': 'capital-sigma', 'mml': '<mi>&#931;</mi>', 'latex': '\\Sigma'},
  {'id': 'capital-tau', 'mml': '<mi>&#932;</mi>', 'latex': 'T'},
  {'id': 'capital-theta', 'mml': '<mi>&#920;</mi>', 'latex': '\\Theta'},
  {'id': 'capital-upsilon', 'mml': '<mi>&#933;</mi>', 'latex': '\\Upsilon'},
  {'id': 'capital-xi', 'mml': '<mi>&#926;</mi>', 'latex': 'X'},
  {'id': 'capital-zeta', 'mml': '<mi>&#918;</mi>', 'latex': 'Z'},
  {
    'id': 'ceeling',
    'mml': '<mfenced open="&#8968;" close="&#8969;"><mi>{{$}}</mi></mfenced>',
    'latex': '\\lceil {{$}} \\rceil'
  },
  {'id': 'chi', 'mml': '<mi>&#967;</mi>', 'latex': '\\chi'},
  {'id': 'circle', 'mml': '<mo>&#9675;</mo>', 'latex': ''},
  {'id': 'circled-asterisk', 'mml': '<mo>&#8859;</mo>', 'latex': '\\circledast'},
  {'id': 'circled-dash', 'mml': '<mo>&#8861;</mo>', 'latex': '\\circleddash'},
  {'id': 'circled-division', 'mml': '<mo>&#10808;</mo>', 'latex': ''},
  {'id': 'circled-dot', 'mml': '<mo>&#8857;</mo>', 'latex': '\\odot'},
  {'id': 'circled-plus', 'mml': '<mo>&#8853;</mo>', 'latex': '\\oplus'},
  {'id': 'circled-times', 'mml': '<mo>&#8855;</mo>', 'latex': '\\otimes'},
  {'id': 'complex-numbers', 'mml': '<mi>&#8450;</mi>', 'latex': '\\mathbb{C}'},
  {'id': 'contains-as-member', 'mml': '<mo>&#8715;</mo>', 'latex': '\\ni'},
  {'id': 'contains-normal-subgroup', 'mml': '<mo>&#8883;</mo>', 'latex': '\\triangleright'},
  {'id': 'contour-integral', 'mml': '<mo>&#8750;</mo>', 'latex': '\\oint'},
  {'id': 'coproduct', 'mml': '<mo>&#8720;</mo>', 'latex': '\\coprod'},
  {'id': 'cosecant', 'mml': '<mi>csc</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\csc({{$}})'},
  {'id': 'cosine', 'mml': '<mi>cos</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\cos({{$}})'},
  {'id': 'cotangent', 'mml': '<mi>cot</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\cot({{$}})'},
  {'id': 'cube-root', 'mml': '<mroot><mi>{{$}}</mi><mn>3</mn></mroot>', 'latex': '\\sqrt[3]{{{$}}}'},
  {'id': 'curl', 'mml': '<mo>&#8711;</mo><mo>&#215;</mo><mi>{{$}}</mi>', 'latex': '\\nabla \\times {{$}}'},
  {
    'id': 'curly-brackets',
    'mml': '<mfenced open="{" close="}"><mi>{{$}}</mi></mfenced>',
    'latex': '\\left \\{ {{$}} \\right \\}'
  },
  {
    'id': 'definite-integral',
    'mml': '<msubsup><mo>&#8747;</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>',
    'latex': '\\int_{{{$}}}^{{{$}}}'
  },
  {
    'id': 'definite-integral-differential',
    'mml': '<msubsup><mo>&#8747;</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup><mi>{{$}}</mi><mo>d</mo><mi>{{$}}</mi>',
    'latex': '\\int_{{{$}}}^{{{$}}}{{$}}\\;\\mathrm{d}{{$}}'
  },
  {'id': 'degree-sign', 'mml': '<mo>&#176;</mo>', 'latex': '^{\\circ}'},
  {'id': 'delta', 'mml': '<mi>&#948;</mi>', 'latex': '\\delta'},
  {
    'id': 'derivative',
    'mml': '<mfrac><mrow><mo>d</mo><mi>{{$}}</mi></mrow><mrow><mo>d</mo><mi>{{$}}</mi></mrow></mfrac>',
    'latex': '\\frac{\\mathrm{d} {{$}}}{\\mathrm{d} {{$}}}'
  },
  {'id': 'diaeresis-accent', 'mml': '<mover><mi>{{$}}</mi><mo>&#168;</mo></mover>', 'latex': '\\ddot{{{$}}}'},
  {'id': 'diamond', 'mml': '<mo>&#8900;</mo>', 'latex': '\\diamond'},
  {'id': 'differential', 'mml': '<mo>d</mo>', 'latex': '\\mathrm{d}'},
  {'id': 'digit-space', 'mml': '<mo>&#8199;</mo>', 'latex': '\\;'},
  {'id': 'divergence', 'mml': '<mo>&#8711;</mo><mo>&#183;</mo><mi>{{$}}</mi>', 'latex': '\\nabla \\cdot {{$}}'},
  {'id': 'division-sign', 'mml': '<mo>&#247;</mo>', 'latex': '\\div'},
  {'id': 'does-not-contain-member', 'mml': '<mo>&#8716;</mo>', 'latex': '\\not\\ni'},
  {'id': 'dot-accent', 'mml': '<mover><mi>{{$}}</mi><mo>&#729;</mo></mover>', 'latex': '\\dot{{{$}}}'},
  {'id': 'double-apostrophe', 'mml': '<mo>\'</mo><mo>\'</mo>', 'latex': '\'\''},
  {'id': 'double-integral', 'mml': '<mo>&#8748;</mo>', 'latex': '\\iint'},
  {
    'id': 'double-vertical-bars',
    'mml': '<mfenced open=' || ' close=' || '><mi>{{$}}</mi></mfenced>',
    'latex': '\\left \\| {{$}} \\right \\|'
  },
  {
    'id': 'down-diagonal-strike',
    'mml': '<menclose notation="downdiagonalstrike"><mi>{{$}}</mi></menclose>',
    'latex': ''
  },
  {'id': 'down-right-diagonal-ellipsis', 'mml': '<mo>&#8945;</mo>', 'latex': '\\ddots'},
  {'id': 'downward-left-corner-arrow', 'mml': '<mo>&#8629;</mo>', 'latex': ''},
  {'id': 'downwards-arrow', 'mml': '<mo>&#8595;</mo>', 'latex': '\\downarrow'},
  {'id': 'downwards-arrow-left-upwards-arrow', 'mml': '<mo>&#8693;</mo>', 'latex': ''},
  {'id': 'downwards-double-arrow', 'mml': '<mo>&#8659;</mo>', 'latex': '\\Downarrow'},
  {'id': 'downwards-harpoon-left-upwards-harpoon', 'mml': '<mo>&#10607;</mo>', 'latex': ''},
  {'id': 'east-west-diagonal-arrow', 'mml': '<mo>&#10530;</mo>', 'latex': ''},
  {'id': 'element-of', 'mml': '<mo>&#8712;</mo>', 'latex': '\\in'},
  {'id': 'element-over', 'mml': '<mover><mi>{{$}}</mi><mi>{{$}}</mi></mover>', 'latex': ''},
  {'id': 'element-under', 'mml': '<munder><mi>{{$}}</mi><mi>{{$}}</mi></munder>', 'latex': ''},
  {
    'id': 'element-underover',
    'mml': '<munderover><mi>{{$}}</mi><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': ''
  },
  {'id': 'ell', 'mml': '<mi>&#8467;</mi>', 'latex': '\\ell'},
  {'id': 'empty-set', 'mml': '<mo>&#8709;</mo>', 'latex': '\\varnothing'},
  {'id': 'enclose-actuarial', 'mml': '<menclose notation="actuarial"><mi>{{$}}</mi></menclose>', 'latex': ''},
  {
    'id': 'enclose-bottom',
    'mml': '<menclose notation="bottom"><mi>{{$}}</mi></menclose>',
    'latex': '\\underline{}'
  },
  {'id': 'enclose-box', 'mml': '<menclose notation="box"><mi>{{$}}</mi></menclose>', 'latex': ''},
  {'id': 'enclose-circle', 'mml': '<menclose notation="circle"><mi>{{$}}</mi></menclose>', 'latex': ''},
  {
    'id': 'enclose-left',
    'mml': '<menclose notation="left"><mi>{{$}}</mi></menclose>',
    'latex': '\\left | {{$}} \\right. '
  },
  {
    'id': 'enclose-right',
    'mml': '<menclose notation="right"><mi>{{$}}</mi></menclose>',
    'latex': '\\left. {{$}} \\right |'
  },
  {'id': 'enclose-rounded-box', 'mml': '<menclose notation="roundedbox"><mi>{{$}}</mi></menclose>', 'latex': ''},
  {'id': 'enclose-top', 'mml': '<menclose notation="top"><mi>{{$}}</mi></menclose>', 'latex': '\\overline{{{$}}}'},
  {'id': 'epsilon', 'mml': '<mi>&#949;</mi>', 'latex': '\\epsilon'},
  {'id': 'equal-operator', 'mml': '<mo>=</mo>', 'latex': '='},
  {'id': 'eta', 'mml': '<mi>&#951;</mi>', 'latex': '\\eta'},
  {'id': 'euler-number', 'mml': '<mi>e</mi>', 'latex': 'e'},
  {'id': 'exp', 'mml': '<msup><mi>e</mi><mi>{{$}}</mi></msup>', 'latex': 'e^{{{$}}}'},
  {'id': 'exponential', 'mml': '<mi>exp</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\exp({{$}})'},
  {'id': 'final-sigma', 'mml': '<mi>&#962;</mi>', 'latex': '\\varsigma'},
  {
    'id': 'floor',
    'mml': '<mfenced open="&#8970;" close="&#8971;"><mi>{{$}}</mi></mfenced>',
    'latex': '\\lfloor {{$}} \\rfloor'
  },
  {'id': 'for-all', 'mml': '<mo>&#8704;</mo>', 'latex': '\\forall'},
  {'id': 'forward-slash', 'mml': '<mo>/</mo>', 'latex': '/'},
  {'id': 'fraction', 'mml': '<mfrac><mi>{{$}}</mi><mi>{{$}}</mi></mfrac>', 'latex': '\\frac{{{$}}}{{{$}}}'},
  {'id': 'gamma', 'mml': '<mi>&#947;</mi>', 'latex': '\\gamma'},
  {'id': 'gradient', 'mml': '<mo>&#8711;</mo><mi>{{$}}</mi>', 'latex': '\\nabla {{$}}'},
  {'id': 'greater-than-not-equal', 'mml': '<mo>&#10888;</mo>', 'latex': '\\gneq'},
  {'id': 'greater-than-or-equal', 'mml': '<mo>&#8805;</mo>', 'latex': '\\geq '},
  {'id': 'greater-than-or-slanted-equal', 'mml': '<mo>&#10878;</mo>', 'latex': '\\geqslant'},
  {'id': 'greater-than-sign', 'mml': '<mo>&gt;</mo>', 'latex': '>'},
  {'id': 'hat-accent', 'mml': '<mover><mi>{{$}}</mi><mo>^</mo></mover>', 'latex': '\\hat{{{$}}}'},
  {'id': 'horizontal-ellipsis', 'mml': '<mo>&#8943;</mo>', 'latex': '\\cdots'},
  {
    'id': 'horizontal-strike',
    'mml': '<menclose notation="horizontalstrike"><mi>{{$}}</mi></menclose>',
    'latex': ''
  },
  {
    'id': 'horizontal-vertical-strikes',
    'mml': '<menclose notation="verticalstrike horizontalstrike"><mi>{{$}}</mi></menclose>',
    'latex': ''
  },
  {'id': 'identical-operator', 'mml': '<mo>&#8801;</mo>', 'latex': '\\equiv'},
  {'id': 'imaginary-numbers', 'mml': '<mi mathvariant="normal">&#120128;</mi>', 'latex': '\\mathbb{I}'},
  {'id': 'imaginary-part', 'mml': '<mo>&#8465;</mo>', 'latex': '\\Im'},
  {'id': 'imaginary-unit-i', 'mml': '<mi>i</mi>', 'latex': 'i'},
  {'id': 'increment', 'mml': '<mo>&#8710;</mo>', 'latex': '\\triangle'},
  {'id': 'infinity', 'mml': '<mo>&#8734;</mo>', 'latex': '\\infty'},
  {'id': 'integer-numbers', 'mml': '<mi mathvariant="normal">&#8484;</mi>', 'latex': '\\mathbb{Z}'},
  {'id': 'integral', 'mml': '<mo>&#8747;</mo>', 'latex': '\\int'},
  {'id': 'integral-subscript', 'mml': '<msub><mo>&#8747;</mo><mi>{{$}}</mi></msub>', 'latex': '\\int_{{$}}'},
  {
    'id': 'integral-subscript-differential',
    'mml': '<msub><mo>&#8747;</mo><mi>{{$}}</mi></msub><mi>{{$}}</mi><mo>d</mo><mi>{{$}}</mi>',
    'latex': '\\int_{{$}}{{$}}\\;\\mathrm{d}{{$}}'
  },
  {'id': 'intersection', 'mml': '<mo>&#8745;</mo>', 'latex': '\\cap'},
  {
    'id': 'inverse-cosecant',
    'mml': '<msup><mi>csc</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': '\\csc^{-1}(x)'
  },
  {
    'id': 'inverse-cosine',
    'mml': '<msup><mi>cos</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': '\\cos^{-1}(x)'
  },
  {
    'id': 'inverse-cotangent',
    'mml': '<msup><mi>cot</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': '\\cot^{-1}(x)'
  },
  {
    'id': 'inverse-secant',
    'mml': '<msup><mi>sec</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': '\\sec^{-1}(x)'
  },
  {
    'id': 'inverse-sine',
    'mml': '<msup><mi>sin</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': '\\sin^{-1}(x)'
  },
  {
    'id': 'inverse-tangent',
    'mml': '<msup><mi>tan</mi><mrow><mo>-</mo><mn>1</mn></mrow></msup><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': '\\tan^{-1}(x)'
  },
  {'id': 'iota', 'mml': '<mi>&#953;</mi>', 'latex': '\\iota'},
  {'id': 'kappa', 'mml': '<mi>&#954;</mi>', 'latex': '\\kappa'},
  {'id': 'lambda', 'mml': '<mi>&#955;</mi>', 'latex': '\\lambda'},
  {'id': 'laplacian', 'mml': '<mo>&#8710;</mo><mi>{{$}}</mi>', 'latex': '\\Delta {{$}}'},
  {
    'id': 'left-angle',
    'mml': '<mfenced open="&lt;" close=""><mi>{{$}}</mi></mfenced>',
    'latex': '\\left \\langle {{$}} \\right.'
  },
  {
    'id': 'left-arrow-over-right-arrow-overscript',
    'mml': '<mover><mo>&#8646;</mo><mi>{{$}}</mi></mover>',
    'latex': ''
  },
  {
    'id': 'left-arrow-over-right-arrow-underscript',
    'mml': '<munder><mo>&#8646;</mo><mi>{{$}}</mi></munder>',
    'latex': ''
  },
  {
    'id': 'left-arrow-over-right-arrow-underoverscript',
    'mml': '<munderover><mo>&#8646;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': ''
  },
  {
    'id': 'left-arrow-overscript',
    'mml': '<mover><mo>&#8592;</mo><mi>{{$}}</mi></mover>',
    'latex': '\\overset{{{$}}}{\\leftarrow}'
  },
  {
    'id': 'left-arrow-underscript',
    'mml': '<munder><mo>&#8592;</mo><mi>{{$}}</mi></munder>',
    'latex': '\\underset{{{$}}}{\\leftarrow}'
  },
  {
    'id': 'left-arrow-underoverscript',
    'mml': '<munderover><mo>&#8592;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': '\\xleftarrow[{{$}}]{{{$}}}'
  },
  {
    'id': 'left-curly-bracket',
    'mml': '<mfenced open="{" close=""><mi>a</mi></mfenced>',
    'latex': '\\left \\{ {{$}} \\right.'
  },
  {
    'id': 'left-harpoon-over-right-harpoon-overscript',
    'mml': '<mover><mo>&#8651;</mo><mi>{{$}}</mi></mover>',
    'latex': '\\overset{{{$}}}{\\leftrightharpoons}'
  },
  {
    'id': 'left-harpoon-over-right-harpoon-underscript',
    'mml': '<munder><mo>&#8651;</mo><mi>{{$}}</mi></munder>',
    'latex': '\\underset{{{$}}}{\\leftrightharpoons}'
  },
  {
    'id': 'left-harpoon-over-right-harpoon-underoverscript',
    'mml': '<munderover><mo>&#8651;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': '\\overset{{{$}}}{\\underset{{{$}}}{\\leftrightharpoons}}'
  },
  {
    'id': 'left-parenthesis',
    'mml': '<mfenced close=""><mi>{{$}}</mi></mfenced>',
    'latex': '\\left ( {{$}} \\right.'
  },
  {'id': 'left-right-arrow', 'mml': '<mo>&#8596;</mo>', 'latex': '\\leftrightarrow'},
  {
    'id': 'left-right-arrow-accent',
    'mml': '<mover><mi>{{$}}</mi><mo>&#8596;</mo></mover>',
    'latex': '\\overset{\\leftrightarrow}{{{$}}}'
  },
  {
    'id': 'left-right-arrow-underoverscript',
    'mml': '<munderover><mo>&#8596;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': '\\overset{{{$}}}{\\underset{{{$}}}{\\leftrightarrow}}'
  },
  {'id': 'left-right-double-arrow', 'mml': '<mo>&#8660;</mo>', 'latex': '\\Leftrightarrow'},
  {
    'id': 'left-square-bracket',
    'mml': '<mfenced open="[" close=""><mi>{{$}}</mi></mfenced>',
    'latex': '\\left [ {{$}} \\right.'
  },
  {
    'id': 'left-subscript',
    'mml': '<mmultiscripts><mi>{{$}}</mi><mprescripts/><mi>{{$}}</mi><none/></mmultiscripts>',
    'latex': ' _{{{$}}}{{$}}'
  },
  {
    'id': 'left-superscript',
    'mml': '<mmultiscripts><mi>{{$}}</mi><mprescripts/><none/><mi>{{$}}</mi></mmultiscripts>',
    'latex': ' ^{{{$}}}{{$}}'
  },
  {
    'id': 'left-superscript-subscript',
    'mml': '<mmultiscripts><mi>{{$}}</mi><mprescripts/><mi>{{$}}</mi><mi>{{$}}</mi></mmultiscripts>',
    'latex': '_{{{$}}}^{{{$}}}{{$}}'
  },
  {'id': 'leftwards-arrow', 'mml': '<mo>&#8592;</mo>', 'latex': '\\leftarrow'},
  {'id': 'leftwards-arrow-from-bar', 'mml': '<mo>&#8612;</mo>', 'latex': ''},
  {'id': 'leftwards-arrow-hook', 'mml': '<mo>&#8617;</mo>', 'latex': '\\hookleftarrow'},
  {'id': 'leftwards-arrow-over-rightwards-arrow', 'mml': '<mo>&#8646;</mo>', 'latex': ''},
  {'id': 'leftwards-double-arrow', 'mml': '<mo>&#8656;</mo>', 'latex': '\\Leftarrow'},
  {'id': 'leftwards-harpoon-barb-downwards', 'mml': '<mo>&#8637;</mo>', 'latex': '\\leftharpoondown'},
  {'id': 'leftwards-harpoon-barb-upwards', 'mml': '<mo>&#8636;</mo>', 'latex': '\\leftharpoonup'},
  {'id': 'leftwards-harpoon-over-dash', 'mml': '<mo>&#10602;</mo>', 'latex': ''},
  {'id': 'leftwards-harpoon-over-rightwards-harpoon', 'mml': '<mo>&#8651;</mo>', 'latex': '\\leftrightharpoons'},
  {'id': 'less-than-not-equal', 'mml': '<mo>&#10887;</mo>', 'latex': '\\lneq'},
  {'id': 'less-than-or-equal', 'mml': '<mo>&#8804;</mo>', 'latex': '\\leq'},
  {'id': 'less-than-or-slanted-equal', 'mml': '<mo>&#10877;</mo>', 'latex': '\\leqslant'},
  {'id': 'less-than-sign', 'mml': '<mo>&lt;</mo>', 'latex': '<'},
  {
    'id': 'limit-infinity',
    'mml': '<munder><mrow><mi>lim</mi></mrow><mrow><mi>{{$}}</mi><mo>&#8594;</mo><mo>&#8734;</mo></mrow></munder>',
    'latex': '\\lim_{{{$}}\\rightarrow \\infty}'
  },
  {
    'id': 'limit-underscript',
    'mml': '<munder><mrow><mi>lim</mi></mrow><mi>{{$}}</mi></munder>',
    'latex': '\\lim_{{{$}}}'
  },
  {'id': 'logarithm', 'mml': '<mi>log</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\log({{$}})'},
  {
    'id': 'logarithm-base-n',
    'mml': '<msub><mi>log</mi><mi>n</mi></msub><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': '\\log_{n}({{$}})'
  },
  {'id': 'logical-and', 'mml': '<mo>&#8743;</mo>', 'latex': '\\land'},
  {'id': 'logical-or', 'mml': '<mo>&#8744;</mo>', 'latex': '\\lor'},
  {
    'id': 'matrix-parenthesis',
    'matrix': true,
    'mml': '<mfenced><mtable>{{$}}</mtable></mfenced>',
    'latex': '\\begin{pmatrix}{{$}}\\end{pmatrix}'
  },
  {
    'id': 'matrix-square-brackets',
    'matrix': true,
    'mml': '<mfenced open="[" close="]"><mtable>{{$}}</mtable></mfenced>',
    'latex': '\\begin{bmatrix}{{$}}\\end{bmatrix}'
  },
  {
    'id': 'matrix-vertical-bars',
    'matrix': true,
    'mml': '<mfenced open="|" close="|"><mtable>{{$}}</mtable></mfenced>',
    'latex': '\\begin{vmatrix}{{$}}\\end{vmatrix}'
  },
  {'id': 'measured-angle', 'mml': '<mo>&#8737;</mo>', 'latex': '\\measuredangle'},
  {'id': 'middle-dot', 'mml': '<mo>&#183;</mo>', 'latex': '\\cdot'},
  {'id': 'minus-plus-sign', 'mml': '<mo>&#8723;</mo>', 'latex': '\\mp'},
  {'id': 'minus-sign', 'mml': '<mo>-</mo>', 'latex': ' - '},
  {'id': 'mu', 'mml': '<mi>&#956;</mi>', 'latex': '\\mu'},
  {'id': 'much-greater-than', 'mml': '<mo>&#8811;</mo>', 'latex': '\\gg'},
  {'id': 'much-less-than', 'mml': '<mo>&#8810;</mo>', 'latex': '\\ll'},
  {'id': 'multiplication-sign', 'mml': '<mo>&#215;</mo>', 'latex': '\\times'},
  {'id': 'nabla', 'mml': '<mo>&#8711;</mo>', 'latex': '\\nabla'},
  {
    'id': 'natural-logarithm',
    'mml': '<mo>ln</mo><mfenced><mi>{{$}}</mi></mfenced>',
    'latex': ' \\ln\\left ( {{$}} \\right ) '
  },
  {'id': 'natural-numbers', 'mml': '<mi mathvariant="normal">&#8469;</mi>', 'latex': '\\mathbb{I} '},
  {'id': 'normal-space', 'mml': '<mo>&#160;</mo>', 'latex': '\\:'},
  {'id': 'normal-subgroup-of', 'mml': '<mo>&#8882;</mo>', 'latex': '\\lhd '},
  {'id': 'north-east-arrow', 'mml': '<mo>&#8599;</mo>', 'latex': '\\nearrow '},
  {'id': 'north-west-arrow', 'mml': '<mo>&#8598;</mo>', 'latex': '\\nwarrow '},
  {'id': 'not-almost-equal', 'mml': '<mo>&#8777;</mo>', 'latex': '\\not\\approx '},
  {'id': 'not-aproximateley-equal', 'mml': '<mo>&#x2247;</mo>', 'latex': '\\not\\cong '},
  {'id': 'not-element-of', 'mml': '<mo>&#8713;</mo>', 'latex': '\\notin '},
  {'id': 'not-equal', 'mml': '<mo>&#8800;</mo>', 'latex': '\\neq'},
  {'id': 'not-identical', 'mml': '<mo>&#8802;</mo>', 'latex': '\\not\\equiv'},
  {'id': 'not-parallel-to', 'mml': '<mo>&#8742;</mo>', 'latex': '\\nparallel'},
  {'id': 'not-sign', 'mml': '<mo>&#172;</mo>', 'latex': '\\neg'},
  {'id': 'not-tilde', 'mml': '<mo>&#8769;</mo>', 'latex': '\\nsim'},
  {'id': 'nu', 'mml': '<mi>&#956;</mi>', 'latex': '\\nu'},
  {'id': 'omega', 'mml': '<mi>&#969;</mi>', 'latex': '\\omega'},
  {'id': 'omicron', 'mml': '<mi>&#959;</mi>', 'latex': 'o'},
  {
    'id': 'overscript-brace',
    'mml': '<mover><mover><mi>{{$}}</mi><mo>&#9182;</mo></mover><mi>{{$}}</mi></mover>',
    'latex': '\\overbrace{{{$}}}^{{{$}}}'
  },
  {'id': 'parallel-to', 'mml': '<mo>&#8741;</mo>', 'latex': '\\parallel'},
  {'id': 'parallelogram', 'mml': '<mo>&#9649;</mo>', 'latex': ''},
  {'id': 'parenthesis', 'mml': '<mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\left ( {{$}} \\right )'},
  {
    'id': 'partial-derivative',
    'mml': '<mfrac><mrow><mo>&#8706;</mo><mi>{{$}}</mi></mrow><mrow><mo>&#8706;</mo><mi>{{$}}</mi></mrow></mfrac>',
    'latex': '\\frac{\\partial {{$}}}{\\partial {{$}}}'
  },
  {
    'id': 'partial-differential',
    'mml': '<mfrac><mrow><mo>d</mo><mi>{{$}}</mi></mrow><mrow><mo>d</mo><mi>{{$}}</mi></mrow></mfrac>',
    'latex': '\\frac{\\mathrm{d} {{$}}}{\\mathrm{d} {{$}}}'
  },
  {'id': 'perpendicular', 'mml': '<mo>&#8869;</mo>', 'latex': '\\perp'},
  {'id': 'phi', 'mml': '<mi>&#966;</mi>', 'latex': '\\phi'},
  {'id': 'phi-alt', 'mml': '<mi>&#981;</mi>', 'latex': '\\varphi'},
  {'id': 'pi', 'mml': '<mi>&#960;</mi>', 'latex': '\\pi'},
  {'id': 'pi-alt', 'mml': '<mi>&#982;</mi>', 'latex': '\\varpi'},
  {'id': 'pi-number', 'mml': '<mi mathvariant="normal">&#960;</mi>', 'latex': '\\pi'},
  {
    'id': 'piecewise-function',
    'mml': '<mfenced open="{" close=""><mtable columnspacing="1.4ex" columnalign="left"><mtr><mtd><mi mathvariant="normal">{{$}}</mi></mtd><mtd><mi mathvariant="normal">{{$}}</mi></mtd></mtr><mtr><mtd><mi mathvariant="normal">{{$}}</mi></mtd><mtd><mi mathvariant="normal">{{$}}</mi></mtd></mtr></mtable></mfenced>',
    'latex': '\\left\\{\\begin{matrix} {{$}} & {{$}} \\\\ {{$}} & {{$}} \\end{matrix}\\right.'
  },
  {'id': 'plus-minus-sign', 'mml': '<mo>&#177;</mo>', 'latex': '\\pm'},
  {'id': 'plus-sign', 'mml': '<mo>+</mo>', 'latex': '+'},
  {'id': 'precedes', 'mml': '<mo>&#8826;</mo>', 'latex': '\\prec'},
  {'id': 'prime-numbers', 'mml': '<mi mathvariant="normal">&#8473;</mi>', 'latex': '\\mathbb{P}'},
  {'id': 'product', 'mml': '<mo>&#8719;</mo>', 'latex': '\\prod'},
  {
    'id': 'product-subscript',
    'mml': '<msub><mo>&#8719;</mo><mi mathvariant="normal">{{$}}</mi></msub>',
    'latex': ''
  },
  {
    'id': 'product-subscript-superscript',
    'mml': '<msubsup><mo>&#8719;</mo><mi mathvariant="normal">{{$}}</mi><mi mathvariant="normal">{{$}}</mi></msubsup>',
    'latex': ''
  },
  {
    'id': 'product-underoverscript',
    'mml': '<munderover><mo>&#8719;</mo><mi mathvariant="normal">{{$}}</mi><mi mathvariant="normal">{{$}}</mi></munderover>',
    'latex': '\\prod_{{{$}}}^{{{$}}}'
  },
  {
    'id': 'product-underscript',
    'mml': '<munder><mo>&#8719;</mo><mi mathvariant="normal">{{$}}</mi></munder>',
    'latex': '\\prod_{{{$}}}'
  },
  {'id': 'proportional-to', 'mml': '<mo>&#8733;</mo>', 'latex': '\\propto'},
  {'id': 'psi', 'mml': '<mi mathvariant="normal">&#968;</mi>', 'latex': '\\psi'},
  {'id': 'quantity-j', 'mml': '<mi>j</mi>', 'latex': '\\jmath'},
  {'id': 'rational-numbers', 'mml': '<mi mathvariant="normal">&#8474;</mi>', 'latex': '\\mathbb{Q}'},
  {'id': 'real-numbers', 'mml': '<mi mathvariant="normal">&#8477;</mi>', 'latex': '\\mathbb{R}'},
  {'id': 'real-part', 'mml': '<mo>&#8476;</mo>', 'latex': '\\Re'},
  {'id': 'reverse-set-minus', 'mml': '<mo>&#8726;</mo>', 'latex': ''},
  {'id': 'reverse-slash', 'mml': '<mo>&#92;</mo>', 'latex': '\\setminus'},
  {'id': 'reversed-prime', 'mml': '<mo>&#8245;</mo>', 'latex': '\\prime'},
  {'id': 'rho', 'mml': '<mi>&#961;</mi>', 'latex': '\\rho'},
  {
    'id': 'right-angle',
    'mml': '<mfenced open="" close="&gt;"><mi>{{$}}</mi></mfenced>',
    'latex': '\\left. {{$}} \\right \\rangle'
  },
  {
    'id': 'right-arrow-over-left-arrow-overscript',
    'mml': '<mover><mo>&#8644;</mo><mi>{{$}}</mi></mover>',
    'latex': ''
  },
  {
    'id': 'right-arrow-over-left-arrow-underscript',
    'mml': '<munder><mo>&#8644;</mo><mi>{{$}}</mi></munder>',
    'latex': ''
  },
  {
    'id': 'right-arrow-over-left-arrow-underoverscript',
    'mml': '<munderover><mo>&#8644;</mo><mi>b</mi><mi>{{$}}</mi></munderover>',
    'latex': ''
  },
  {
    'id': 'right-arrow-overscript',
    'mml': '<mover><mo>&#8594;</mo><mi>{{$}}</mi></mover>',
    'latex': '\\overset{{{$}}}{\\rightarrow}'
  },
  {
    'id': 'right-arrow-subscript',
    'mml': '<munder><mo>&#8594;</mo><mi>{{$}}</mi></munder>',
    'latex': '\\underset{{{$}}}{\\rightarrow}'
  },
  {
    'id': 'right-arrow-underoverscript',
    'mml': '<munderover><mo>&#8594;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': '\\xrightarrow[{{$}}]{{{$}}}'
  },
  {
    'id': 'right-curly-bracket',
    'mml': '<mfenced open="" close="}"><mi>{{$}}</mi></mfenced>',
    'latex': '\\left. {{$}} \\right \\}'
  },
  {
    'id': 'right-harpoon-over-left-harpoon-overscript',
    'mml': '<mover><mo>&#8652;</mo><mi>{{$}}</mi></mover>',
    'latex': '\\overset{{{$}}}{\\rightleftharpoons}'
  },
  {
    'id': 'right-harpoon-over-left-harpoon-underoverscript',
    'mml': '<munderover><mo>&#8652;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': '\\overset{{{$}}}{\\underset{{{$}}}{\\rightleftharpoons}}'
  },
  {
    'id': 'right-harpoon-over-left-harpoon-underscript',
    'mml': '<munder><mo>&#8652;</mo><mi>{{$}}</mi></munder>',
    'latex': '\\underset{{{$}}}{\\rightleftharpoons}'
  },
  {
    'id': 'right-left-arrow-overscript',
    'mml': '<mover><mo>&#8596;</mo><mi>{{$}}</mi></mover>',
    'latex': '\\overset{{{$}}}{\\leftrightarrow}'
  },
  {
    'id': 'right-left-arrow-underscript',
    'mml': '<munder><mo>&#8596;</mo><mi>{{$}}</mi></munder>',
    'latex': '\\underset{{{$}}}{\\leftrightarrow}'
  },
  {
    'id': 'right-parenthesis',
    'mml': '<mfenced open="" close=")"><mi>{{$}}</mi></mfenced>',
    'latex': '\\left. {{$}} \\right )'
  },
  {
    'id': 'right-square-bracket',
    'mml': '<mfenced open="" close="]"><mi>{{$}}</mi></mfenced>',
    'latex': '\\left. {{$}} \\right ]'
  },
  {'id': 'rightwards-arrow-from-bar', 'mml': '<mo>&#8614;</mo>', 'latex': '\\mapsto'},
  {'id': 'rightwards-arrow-hook', 'mml': '<mo>&#8618;</mo>', 'latex': '\\hookrightarrow'},
  {'id': 'rightwards-arrow-over-leftwards-arrow', 'mml': '<mo>&#8644;</mo>', 'latex': ''},
  {'id': 'rightwards-double-arrow', 'mml': '<mo>&#8658;</mo>', 'latex': '\\Rightarrow'},
  {'id': 'rightwards-harpoon-barb-downwards', 'mml': '<mo>&#8641;</mo>', 'latex': '\\rightharpoondown'},
  {'id': 'rightwards-harpoon-below-dash', 'mml': '<mo>&#10605;</mo>', 'latex': ''},
  {'id': 'rightwards-harpoon-over-leftwards-harpoon', 'mml': '<mo>&#8652;</mo>', 'latex': '\\rightleftharpoons'},
  {'id': 'righwards-arrow', 'mml': '<mo>&#8594;</mo>', 'latex': '\\rightarrow'},
  {'id': 'righwards-harpoon-barb-upwards', 'mml': '<mo>&#8640;</mo>', 'latex': '\\rightharpoonup'},
  {'id': 'ring-operator', 'mml': '<mo>&#8728;</mo>', 'latex': '\\circ'},
  {'id': 'root', 'mml': '<mroot><mi>{{$}}</mi><mn>{{$}}</mn></mroot>', 'latex': '\\sqrt[{{$}}]{{{$}}}'},
  {'id': 'script-capital-f', 'mml': '<mo>&#8497;</mo>', 'latex': '\\mathcal{F}'},
  {'id': 'script-capital-l', 'mml': '<mo>&#8466;</mo>', 'latex': '\\mathcal{L}'},
  {'id': 'script-capital-p', 'mml': '<mi>&#8472;</mi>', 'latex': '\\wp'},
  {'id': 'secant', 'mml': '<mi>sec</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\sec({{$}})'},
  {'id': 'sigma', 'mml': '<mi>&#963;</mi>', 'latex': '\\sigma'},
  {'id': 'sine', 'mml': '<mi>sin</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\sin({{$}})'},
  {'id': 'single-apostrophe', 'mml': '<mo>\'</mo>', 'latex': '\''},
  {
    'id': 'small-fraction',
    'mml': '<mstyle displaystyle="false"><mfrac><mi>{{$}}</mi><mi>{{$}}</mi></mfrac></mstyle>',
    'latex': '\\tfrac{{{$}}}{{{$}}}'
  },
  {'id': 'south-east-arrow', 'mml': '<mo>&#8600;</mo>', 'latex': '\\searrow'},
  {'id': 'south-west-arrow', 'mml': '<mo>&#8601;</mo>', 'latex': '\\swarrow'},
  {'id': 'spherical-angle', 'mml': '<mo>&#8738;</mo>', 'latex': '\\sphericalangle'},
  {'id': 'square', 'mml': '<mo>&#9633;</mo>', 'latex': '\\square'},
  {
    'id': 'square-brackets',
    'mml': '<mfenced open="[" close="]"><mi>{{$}}</mi></mfenced>',
    'latex': '\\left [ {{$}} \\right ]'
  },
  {'id': 'square-cap', 'mml': '<mo>&#8851;</mo>', 'latex': '\\sqcap'},
  {'id': 'square-cup', 'mml': '<mo>&#8852;</mo>', 'latex': '\\sqcup'},
  {'id': 'square-root', 'mml': '<msqrt><mi>{{$}}</mi></msqrt>', 'latex': '\\sqrt{{{$}}}'},
  {'id': 'square-subset-of', 'mml': '<mo>&#8847;</mo>', 'latex': '\\sqsubset'},
  {'id': 'square-subset-or-equal', 'mml': '<mo>&#8849;</mo>', 'latex': '\\sqsubseteq'},
  {'id': 'square-superset-of', 'mml': '<mo>&#8848;</mo>', 'latex': '\\sqsupset'},
  {'id': 'square-superset-or-equal', 'mml': '<mo>&#8850;</mo>', 'latex': '\\sqsupseteq'},
  {'id': 'subscript', 'mml': '<msub><mi>{{$}}</mi><mi>{{$}}</mi></msub>', 'latex': '{{$}}_{{{$}}}'},
  {'id': 'subset-of', 'mml': '<mo>&#8834;</mo>', 'latex': '\\subset'},
  {'id': 'subset-of-or-equal-to', 'mml': '<mo>&#8838;</mo>', 'latex': '\\subseteq'},
  {'id': 'succeedes', 'mml': '<mo>&#8827;</mo>', 'latex': '\\succ'},
  {'id': 'sum', 'mml': '<mo>&#8721;</mo>', 'latex': '\\sum'},
  {'id': 'sum-subscript', 'mml': '<msub><mo>&#8721;</mo><mi>{{$}}</mi></msub>', 'latex': ''},
  {
    'id': 'sum-subscript-superscript',
    'mml': '<msubsup><mo>&#8721;</mo><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>',
    'latex': ''
  },
  {
    'id': 'sum-underoverscript',
    'mml': '<munderover><mo>&#8721;</mo><mi>{{$}}</mi><mi>{{$}}</mi></munderover>',
    'latex': '\\sum_{{{$}}}^{{{$}}}'
  },
  {'id': 'sum-underscript', 'mml': '<munder><mo>&#8721;</mo><mi>{{$}}</mi></munder>', 'latex': '\\sum_{{{$}}}'},
  {'id': 'superscript', 'mml': '<msup><mi>{{$}}</mi><mi>{{$}}</mi></msup>', 'latex': '{{$}}^{{{$}}}'},
  {
    'id': 'superscript-subscript',
    'mml': '<msubsup><mi>{{$}}</mi><mi>{{$}}</mi><mi>{{$}}</mi></msubsup>',
    'latex': '{{$}}_{{{$}}}^{{{$}}}'
  },
  {'id': 'superset-of', 'mml': '<mo>&#8835;</mo>', 'latex': '\\supset'},
  {'id': 'superset-of-or-equal-to', 'mml': '<mo>&#8839;</mo>', 'latex': '\\supseteq'},
  {'id': 'surface-integral', 'mml': '<mo>&#8751;</mo>', 'latex': '\\oint'},
  {'id': 'table', 'mml': '<mtable>{{$}}</mtable>', 'matrix': true, 'latex': '\\begin{matrix}{{$}}\\end{matrix}'},
  {'id': 'tangent', 'mml': '<mi>tan</mi><mfenced><mi>{{$}}</mi></mfenced>', 'latex': '\\tan({{$}})'},
  {'id': 'tau', 'mml': '<mi>&#964;</mi>', 'latex': '\\tau'},
  {'id': 'there-exists', 'mml': '<mo>&#8707;</mo>', 'latex': '\\exists'},
  {'id': 'there-not-exists', 'mml': '<mo>&#8708;</mo>', 'latex': '\\nexists'},
  {'id': 'therefore', 'mml': '<mo>&#8756;</mo>', 'latex': '\\therefore'},
  {'id': 'theta', 'mml': '<mi>&#952;</mi>', 'latex': '\\theta'},
  {'id': 'theta-alt', 'mml': '<mi>&#977;</mi>', 'latex': '\\vartheta'},
  {'id': 'thinner-space', 'mml': '<mo>&#8202;</mo>', 'latex': '\\,'},
  {
    'id': 'three-column-row',
    'mml': '<mtable><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable>',
    'latex': '\\begin{matrix} {{$}} & {{$}} & {{$}} \\end{matrix}'
  },
  {
    'id': 'three-row-column',
    'mml': '<mtable><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable>',
    'latex': '\\begin{matrix} {{$}} \\\\ {{$}} \\\\ {{$}} \\end{matrix}'
  },
  {'id': 'tilde-accent', 'mml': '<mover><mi>{{$}}</mi><mo>~</mo></mover>', 'latex': '\\widetilde{{{$}}}'},
  {'id': 'tilde-operator', 'mml': '<mo>~</mo>', 'latex': '\\sim'},
  {
    'id': 'top-curly-bracket',
    'mml': '<mover><mrow><mi>{{$}}</mi></mrow><mo>&#9182;</mo></mover>',
    'latex': '\\overbrace{{{$}}}'
  },
  {'id': 'top-parenthesis', 'mml': '<mover><mi>{{$}}</mi><mo>&#9180;</mo></mover>', 'latex': ''},
  {'id': 'triangle', 'mml': '<mo>&#9651;</mo>', 'latex': '\\triangle'},
  {'id': 'triple-integral', 'mml': '<mo>&#8749;</mo>', 'latex': '\\iiint'},
  {
    'id': 'two-column-row-parenthesis',
    'mml': '<mfenced><mtable><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>',
    'latex': '\\begin{pmatrix} {{$}} & {{$}} \\end{pmatrix}'
  },
  {
    'id': 'two-column-row-square-bracket',
    'mml': '<mfenced open="[" close="]"><mtable><mtr><mtd><mi>{{$}}</mi></mtd><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>',
    'latex': '\\begin{bmatrix} {{$}} & {{$}} \\end{bmatrix}'
  },
  {
    'id': 'two-row-column-left-curly-bracket',
    'mml': '<mfenced open="{" close=""><mtable columnalign="left"><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>',
    'latex': '\\left\\{\\begin{matrix} {{$}} \\\\ {{$}} \\end{matrix}\\right.'
  },
  {
    'id': 'two-row-column-parenthesis',
    'mml': '<mfenced><mtable><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>',
    'latex': '\\binom{{{$}}}{{{$}}}'
  },
  {
    'id': 'two-row-column-square-brackets',
    'mml': '<mfenced open="[" close="]"><mtable><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>',
    'latex': '\\begin{bmatrix} {{$}} \\\\ {{$}} \\end{bmatrix}'
  },
  {
    'id': 'two-rows-column-right-curly-brackets',
    'mml': '<mfenced open="" close="}"><mtable columnalign="right"><mtr><mtd><mi>{{$}}</mi></mtd></mtr><mtr><mtd><mi>{{$}}</mi></mtd></mtr></mtable></mfenced>',
    'latex': '\\left.\\begin{matrix} {{$}} \\ {{$}} \\end{matrix}\\right\\}'
  },
  {
    'id': 'underscript-brace',
    'mml': '<munder><munder><mi>{{$}}</mi><mo>&#9183;</mo></munder><mrow><mi>{{$}}</mi></mrow></munder>',
    'latex': '\\underbrace{{{$}}}_{{{$}}}'
  },
  {'id': 'union', 'mml': '<mo>&#8746;</mo>', 'latex': '\\cup'},
  {
    'id': 'up-diagonal-strike',
    'mml': '<menclose notation="updiagonalstrike"><mi>{{$}}</mi></menclose>',
    'latex': ''
  },
  {'id': 'up-down-arrow', 'mml': '<mo>&#8597;</mo>', 'latex': '\\updownarrow'},
  {
    'id': 'up-down-diagonal-strike',
    'mml': '<menclose notation="downdiagonalstrike updiagonalstrike"><mi>{{$}}</mi></menclose>',
    'latex': ''
  },
  {'id': 'up-down-double-arrow', 'mml': '<mo>&#8661;</mo>', 'latex': '\\Updownarrow'},
  {'id': 'up-right-diagonal-ellipsis', 'mml': '<mo>&#8944;</mo>', 'latex': ''},
  {'id': 'upsilon', 'mml': '<mi>&#965;</mi>', 'latex': '\\upsilon'},
  {'id': 'upwards-arrow', 'mml': '<mo>&#8593;</mo>', 'latex': '\\uparrow'},
  {'id': 'upwards-arrow-left-downwards-arrow', 'mml': '<mo>&#8645;</mo>', 'latex': '\\uparrow\\!\\downarrow'},
  {'id': 'upwards-double-arrow', 'mml': '<mo>&#8657;</mo>', 'latex': '\\Uparrow'},
  {'id': 'upwards-harpoon-left-downwards-harpoon', 'mml': '<mo>&#10606;</mo>', 'latex': ''},
  {'id': 'vector-accent', 'mml': '<mover><mi>{{$}}</mi><mo>&#8640;</mo></mover>', 'latex': '\\vec{{{$}}}'},
  {
    'id': 'vertical-bars',
    'mml': '<mfenced open="|" close="|"><mi>{{$}}</mi></mfenced>',
    'latex': '\\left | {{$}} \\right |'
  },
  {'id': 'vertical-ellipsis', 'mml': '<mo>&#8942;</mo>', 'latex': '\\vdots'},
  {'id': 'vertical-strike', 'mml': '<menclose notation="horizontalstrike"><mi>{{$}}</mi></menclose>', 'latex': ''},
  {'id': 'volume-integral', 'mml': '<mo>&#8752;</mo>', 'latex': ''},
  {'id': 'west-east-diagonal-arrow', 'mml': '<mo>&#10529;</mo>', 'latex': '\\wearrow'},
  {'id': 'xi', 'mml': '<mi>&#958;</mi>', 'latex': '\\xi'},
  {'id': 'z-transform', 'mml': '<mo>&#437;</mo>', 'latex': '\\mathcal{Z}'},
  {'id': 'zeta', 'mml': '<mi>&#950;</mi>', 'latex': '\\zeta'}
]
