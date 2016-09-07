/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class GeneralParametersCtrl {
  constructor ($state, CourseService) {
    this.$state = $state
    this.CourseService = CourseService
    this.configs = {
      disableInvitations: false,
      disableCertificates: false,
      enableCoursesProfileTab: false
    }
    this.initialize()
  }

  initialize () {
    this.CourseService.getGeneralParameters().then(d => {
      this.configs['disableInvitations'] = d['disableInvitations']
      this.configs['disableCertificates'] = d['disableCertificates']
      this.configs['enableCoursesProfileTab'] = d['enableCoursesProfileTab']
    })
  }

  validate () {
    this.CourseService.setGeneralParameters(this.configs).then(d => {
      if (d === 'success') {
        this.$state.go('configuration')
      }
    })
  }
}