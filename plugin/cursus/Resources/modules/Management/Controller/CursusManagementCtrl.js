/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class CursusManagementCtrl {
  constructor($stateParams, CursusService) {
    this.CursusService = CursusService
    this.cursus = CursusService.getCursus()
    this.hierarchy = CursusService.getHierarchy()
    this.cursusId = $stateParams.cursusId
    this.breadCrumbLabel = ''
    this.initialize()
  }

  initialize() {
    const init = this.CursusService.initialize(this.cursusId)

    if (init !== null) {
      init.then(d => {
        if (d === 'initialized' && this.cursus.length > 0) {
          this.breadCrumbLabel = this.cursus[0]['title']
        }
      })
    }
  }

  editCursus(cursus) {
    this.CursusService.editCursus(cursus)
  }

  createChildCursus(cursusId) {
    this.CursusService.createCursus(cursusId)
  }

  deleteCursus(cursusId) {
    this.CursusService.deleteCursus(cursusId)
  }

  createCourse(cursusId) {
    this.CursusService.createCursusCourse(cursusId)
  }

  addCourse(cursusId, title) {
    this.CursusService.showCoursesListForCursus(cursusId, title)
  }

  removeCourse(cursusId) {
    this.CursusService.removeCourseFromCursus(cursusId)
  }
}