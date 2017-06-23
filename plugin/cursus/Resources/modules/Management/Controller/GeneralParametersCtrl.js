/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class GeneralParametersCtrl {
  constructor($state, CourseService) {
    this.$state = $state
    this.CourseService = CourseService
    this.configs = {
      disableInvitations: false,
      disableCertificates: false,
      disableSessionEventRegistration: false,
      enableCoursesProfileTab: false,
      enableWsInCoursesProfileTab: false,
      sessionDefaultTotal: null,
      displayUserEventsInDesktopAgenda: false
    }
    this.initialize()
  }

  initialize() {
    this.CourseService.getGeneralParameters().then(d => {
      this.configs['disableInvitations'] = d['disableInvitations']
      this.configs['disableCertificates'] = d['disableCertificates']
      this.configs['disableSessionEventRegistration'] = d['disableSessionEventRegistration']
      this.configs['enableCoursesProfileTab'] = d['enableCoursesProfileTab']
      this.configs['enableWsInCoursesProfileTab'] = d['enableWsInCoursesProfileTab']
      this.configs['sessionDefaultTotal'] = parseInt(d['sessionDefaultTotal'])
      this.configs['sessionDefaultDuration'] = parseInt(d['sessionDefaultDuration'])
      this.configs['displayUserEventsInDesktopAgenda'] = d['displayUserEventsInDesktopAgenda']
    })
  }

  validate() {
    this.CourseService.setGeneralParameters(this.configs).then(d => {
      if (d === 'success') {
        this.$state.go('configuration')
      }
    })
  }
}