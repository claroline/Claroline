/**
 * Correction module
 */

import angular from 'angular/index'
import 'angular-ui-translation/angular-translation'
import '#/main/core/translation/module'
import './../common/module'
import './../question/module'

import CorrectionMode from './Services/CorrectionMode'
import MarkMode from './Services/MarkMode'
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
    'translation',
    'ui.translation',
    'Common',
    'Question'
  ])
  .service('CorrectionMode', [
    'Translator',
    CorrectionMode
  ])
  .service('MarkMode', [
    'Translator',
    MarkMode
  ])
  .controller('ChoiceCorrectionCtrl', [
    'QuestionService',
    'ChoiceQuestionService',
    ChoiceCorrectionCtrl
  ])
  .controller('ClozeCorrectionCtrl', [
    'QuestionService',
    'ClozeQuestionService',
    ClozeCorrectionCtrl
  ])
  .controller('GraphicCorrectionCtrl', [
    'QuestionService',
    'GraphicQuestionService',
    'ImageAreaService',
    GraphicCorrectionCtrl
  ])
  .controller('MatchCorrectionCtrl', [
    'QuestionService',
    'MatchQuestionService',
    MatchCorrectionCtrl
  ])
  .controller('OpenCorrectionCtrl', [
    'QuestionService',
    'OpenQuestionService',
    OpenCorrectionCtrl
  ])
  .directive('choiceCorrection', [
    ChoiceCorrectionDirective
  ])
  .directive('clozeCorrection', [
    '$compile',
    ClozeCorrectionDirective
  ])
  .directive('graphicCorrection', [
    GraphicCorrectionDirective
  ])
  .directive('matchCorrection', [
    MatchCorrectionDirective
  ])
  .directive('openCorrection', [
    OpenCorrectionDirective
  ])
