/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class CoursesManagementCtrl {
  constructor(NgTableParams, CourseService, SessionService) {
    this.CourseService = CourseService
    this.SessionService = SessionService
    this.courses = CourseService.getCourses()
    this.sessions = SessionService.getOpenCourseSessions()
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.courses}
    )
    this.isCollapsed = {}
    this._addCourseCallback = this._addCourseCallback.bind(this)
    this._updateCourseCallback = this._updateCourseCallback.bind(this)
    this._removeCourseCallback = this._removeCourseCallback.bind(this)
    this.initialize()
  }

  _addCourseCallback(data) {
    const coursesJson = JSON.parse(data)

    if (Array.isArray(coursesJson)) {
      coursesJson.forEach(c => {
        this.courses.push(c)
      })
    } else {
      this.courses.push(coursesJson)
    }
    this.tableParams.reload()
  }

  _updateCourseCallback(data) {
    const courseJson = JSON.parse(data)
    const index = this.courses.findIndex(c => c['id'] === courseJson['id'])

    if (index > -1) {
      this.courses[index] = courseJson
      this.tableParams.reload()
    }
  }

  _removeCourseCallback(data) {
    const courseJson = JSON.parse(data)
    const index = this.courses.findIndex(c => c['id'] === courseJson['id'])

    if (index > -1) {
      this.courses.splice(index, 1)
      this.tableParams.reload()
    }
  }

  initialize() {
    this.CourseService.loadCourses()
  }

  isInitialized() {
    return this.CourseService.isInitialized()
  }

  createCourse() {
    this.CourseService.createCourse(null, this._addCourseCallback)
  }

  editCourse(courseId) {
    this.CourseService.editCourse(courseId, this._updateCourseCallback)
  }

  deleteCourse(courseId) {
    this.CourseService.deleteCourse(courseId, this._removeCourseCallback)
  }

  viewCourse(courseId) {
    this.CourseService.viewCourse(courseId)
  }

  importCourses() {
    this.CourseService.importCourses(this._addCourseCallback)
  }

  loadSessions(courseId) {
    this.SessionService.loadSessionsByCourse(courseId)
  }

  createSession(course) {
    this.loadSessions(course['id'])
    this.SessionService.createSession(course)
  }

  editSession(session) {
    this.SessionService.editSession(session)
  }

  deleteSession(sessionId) {
    this.SessionService.deleteSession(sessionId)
  }

  sendMessageToSessionLearners(session) {
    this.SessionService.sendMessageToSession(session)
  }
}