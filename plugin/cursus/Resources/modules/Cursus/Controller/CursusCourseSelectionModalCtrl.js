/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class CursusCourseSelectionModalCtrl {
  constructor(NgTableParams, CursusService, CourseService, cursusId, title) {
    this.CursusService = CursusService
    this.CourseService = CourseService
    this.courses = CourseService.getCourses()
    this.cursusId = cursusId
    this.title = title
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.courses}
    )
    CourseService.loadCourses(this.cursusId)
    this._removeCourseCallback = this._removeCourseCallback.bind(this)
  }

  _removeCourseCallback(courseId) {
    this.CourseService.removeCourse(courseId)
    this.tableParams.reload()
  }

  isInitialized() {
    return this.CourseService.isInitialized()
  }

  addCourseToCursus(courseId) {
    this.CursusService.addCourseToCursus(this.cursusId, courseId, this._removeCourseCallback)
  }
}
