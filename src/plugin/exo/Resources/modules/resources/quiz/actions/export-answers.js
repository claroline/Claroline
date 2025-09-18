import {constants, declareAction} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((resourceNodes) => ({
  name: 'export-answers',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-file-csv',
  label: trans('export_csv_answers', {}, 'quiz'),
  title: trans('export_csv_answers', {}, 'quiz'),
  labelShort: trans('export', {}, 'actions'),
  description: trans('export_csv_answers_help', {}, 'quiz'),
  displayed: hasPermission('follow', resourceNodes[0]),
  request: {
    url: ['exercise_papers_export_csv', {quizId: resourceNodes[0].id}]
  },
  group: trans('transfer'),
  scope: [constants.ACTION_SCOPE_OBJECT],
  set: [constants.ACTION_SET_DASHBOARD, constants.ACTION_SET_ADVANCED]
}))
