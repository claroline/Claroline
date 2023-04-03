import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'


/**
 * Export course into PDF file.
 */
export default (courses) => ({
  name: 'export-pdf',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-file-pdf',
  label: trans('export-pdf', {}, 'actions'),
  displayed: hasPermission('open', courses[0]),
  group: trans('transfer'),
  target: ['apiv2_cursus_course_download_pdf', {id: courses[0].id}],
  scope: ['object']
})
