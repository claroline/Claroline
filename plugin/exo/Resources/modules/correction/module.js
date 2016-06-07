/**
 * Correction module
 */

import 'angular-ui-translation'
import './../common/module'

import AbstractCorrectionCtrl from './Controllers/AbstractCorrectionCtrl'
import ChoiceCorrectionCtrl from './Controllers/ChoiceCorrectionCtrl'
import ClozeCorrectionCtrl from './Controllers/ClozeCorrectionCtrl'
import GraphicCorrectionCtrl from './Controllers/GraphicCorrectionCtrl'
import MatchCorrectionCtrl from './Controllers/MatchCorrectionCtrl'
import OpenCorrectionCtrl from './Controllers/OpenCorrectionCtrl'

import ChoiceCorrectionDirective from './Directives/ChoiceCorrectionDirective'
import ClozeCorrectionDirective from './Directives/ClozeCorrectionDirective'
import GraphicCorrectionDirective from './Directives/GraphicCorrectionDirective'
import MatchCorrectionDirective from './Directives/MatchCorrectionDirective'
import OpenCorrectionDirective from './Directives/OpenCorrectionDirective'

angular
    .module('Correction', [
        'ui.translation',
        'Common'
    ])
    .controller('AbstractCorrectionCtrl', ['QuestionService', AbstractCorrectionCtrl])
    .controller('ChoiceCorrectionCtrl', ['QuestionService', 'ChoiceQuestionService', ChoiceCorrectionCtrl])
    .controller('ClozeCorrectionCtrl', ['QuestionService', 'ClozeQuestionService', ClozeCorrectionCtrl])
    .controller('GraphicCorrectionCtrl', ['QuestionService', 'GraphicQuestionService', 'ImageAreaService', GraphicCorrectionCtrl])
    .controller('MatchCorrectionCtrl', ['QuestionService', 'MatchQuestionService', MatchCorrectionCtrl])
    .controller('OpenCorrectionCtrl', ['QuestionService', 'OpenQuestionService', OpenCorrectionCtrl])
    .directive('choiceCorrection', [ChoiceCorrectionDirective])
    .directive('clozeCorrection', ['$compile', ClozeCorrectionDirective])
    .directive('graphicCorrection', [GraphicCorrectionCtrl])
    .directive('correctionMatch', [MatchCorrectionDirective])
    .directive('openCorrection', [OpenCorrectionDirective])
