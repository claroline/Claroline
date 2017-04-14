/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class SessionCreationCoursesListModalCtrl {
  constructor($http, $uibModalInstance, NgTableParams, CourseService, SessionService, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.CourseService = CourseService
    this.SessionService = SessionService
    this.callback = callback
    this.courses = CourseService.getCourses()
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.courses}
    )
    this.CourseService.loadCourses()
  }

  selectCourse(courseId) {
    const course = this.courses.find(c => c['id'] === courseId)
    this.$uibModalInstance.close()
    this.SessionService.createSession(course, this.callback)
  }
}
