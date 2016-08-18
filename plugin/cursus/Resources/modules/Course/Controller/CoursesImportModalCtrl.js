/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/

export default class CoursesImportModalCtrl {
  constructor($http, $uibModal, $uibModalInstance, callback, FormBuilderService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.callback = callback
    this.file = {}
    this.FormBuilderService = FormBuilderService
    this.errorMsg = null
  }

  submit() {
    this.errorMsg = null
    this.FormBuilderService.submit(Routing.generate('api_post_courses_import'), {archive: this.file}).then(
      d => {
        this.callback(d['data'])
        this.$uibModalInstance.close()
      },
      () => {
        this.errorMsg = Translator.trans('courses_import_error_from_file', {}, 'cursus')
      }
    )
  }
}
