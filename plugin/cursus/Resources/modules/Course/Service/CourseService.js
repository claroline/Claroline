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
import angular from 'angular/index'
import $ from 'jquery'
import courseFormTemplate from '../Partial/course_form_modal.html'
import courseViewTemplate from '../Partial/course_view_modal.html'
import coursesImportFormTemplate from '../Partial/courses_import_form.html'

export default class CourseService {
  constructor($http, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.course = {}
    this.courses = []
    this.initialized = false
    this.currentCursusId = null,
    this.hasChanged = false
    this._addCourseCallback = this._addCourseCallback.bind(this)
    this._updateCourseCallback = this._updateCourseCallback.bind(this)
    this._removeCourseCallback = this._removeCourseCallback.bind(this)
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
  }

  _updateCourseCallback(data) {
    const courseJson = JSON.parse(data)
    const index = this.courses.findIndex(c => c['id'] === courseJson['id'])

    if (index > -1) {
      this.courses[index] = courseJson
    }
  }

  _removeCourseCallback(data) {
    const courseJson = JSON.parse(data)
    const index = this.courses.findIndex(c => c['id'] === courseJson['id'])

    if (index > -1) {
      this.courses.splice(index, 1)
    }
  }

  getCourse() {
    return this.course
  }

  getCourses() {
    return this.courses
  }

  isInitialized() {
    return this.initialized
  }

  loadCourses(cursusId = null) {
    if (this.initialized && !this.hasChanged && this.currentCursusId === cursusId) {
      return null
    } else {
      this.initialized = false
      this.courses.splice(0, this.courses.length)
      const route = cursusId ?
        Routing.generate('claroline_cursus_unmapped_courses_retrieve', {cursus: cursusId}) :
        Routing.generate('claroline_cursus_all_courses_retrieve')

      return this.$http.get(route).then(d => {
        if (d['status'] === 200) {
          angular.merge(this.courses, JSON.parse(d['data']))
          this.currentCursusId = cursusId
          this.hasChanged = false
          this.initialized = true

          return 'initialized'
        }
      })
    }
  }

  removeCourse(courseId) {
    const index = this.courses.findIndex(c => c['id'] === courseId)

    if (index > -1) {
      this.courses.splice(index, 1)
      this.hasChanged = true
    }
  }

  createCourse(cursusId = null, callback = null) {
    const addCallback = callback !== null ? callback : this._addCourseCallback
    this.$uibModal.open({
      template: courseFormTemplate,
      controller: 'CourseCreationModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('course_creation', {}, 'cursus') },
        cursusId: () => { return cursusId },
        callback: () => { return addCallback }
      }
    })
  }

  editCourse(course, callback = null) {
    const updateCallback = callback !== null ? callback : this._updateCourseCallback
    this.$uibModal.open({
      template: courseFormTemplate,
      controller: 'CourseEditionModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('course_edition', {}, 'cursus') },
        course: () => { return course },
        callback: () => { return updateCallback }
      }
    })
  }

  deleteCourse(courseId, callback = null) {
    const url = Routing.generate('api_delete_course', {course: courseId})
    const deleteCallback = callback !== null ? callback : this._removeCourseCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('delete_course', {}, 'cursus'),
      Translator.trans('delete_course_confirm_message', {}, 'cursus')
    )
  }

  viewCourse(courseId) {
    const index = this.courses.findIndex(c => c['id'] === courseId)

    if (index > -1) {
      this.$uibModal.open({
        template: courseViewTemplate,
        controller: 'CourseViewModalCtrl',
        controllerAs: 'cmc',
        resolve: {
          course: () => { return this.courses[index] }
        }
      })
    }
  }

  importCourses(callback = null) {
    const addCallback = callback !== null ? callback : this._addCourseCallback
    this.$uibModal.open({
      template: coursesImportFormTemplate,
      controller: 'CoursesImportModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        callback: () => { return addCallback }
      }
    })
  }

  getCourseById(courseId) {
    const index = this.courses.findIndex(c => c['id'] === courseId)

    if (index > -1) {
      this.course = this.courses[index]

      return 'initialized'
    } else {
      for (const key in this.course) {
        delete this.course[key]
      }
      const route = Routing.generate('api_get_course_by_id', {course: courseId})
      return this.$http.get(route).then(d => {
        if (d['status'] === 200) {
          const datas = JSON.parse(d['data'])

          for (const key in datas) {
            this.course[key] = datas[key]
          }

          return 'initialized'
        }
      })
    }
  }

  getTinymceConfiguration() {
    let tinymce = window.tinymce
    tinymce.claroline.init = tinymce.claroline.init || {}
    tinymce.claroline.plugins = tinymce.claroline.plugins || {}

    let plugins = [
      'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars fullscreen',
      'insertdatetime media nonbreaking save table directionality',
      'template paste textcolor emoticons code -accordion -mention -codemirror'
    ]
    let toolbar = 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fullscreen displayAllButtons'

    $.each(tinymce.claroline.plugins, (key, value) => {
      if ('autosave' != key &&  value === true) {
        plugins.push(key)
        toolbar += ' ' + key
      }
    })

    let config = {}

    for (const prop in tinymce.claroline.configuration) {
      if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
        config[prop] = tinymce.claroline.configuration[prop]
      }
    }
    config.plugins = plugins
    config.toolbar1 = toolbar
    config.trusted = true
    config.format = 'html'

    return config
  }

  removeFromArray(targetArray, id) {
    if (Array.isArray(targetArray)) {
      const index = targetArray.findIndex(t => t['id'] === id)

      if (index > -1) {
        targetArray.splice(index, 1)
      }
    }
  }

  getGeneralParameters() {
    const url = Routing.generate('api_get_cursus_general_parameters')
    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return d['data']
      }
    })
  }

  setGeneralParameters(params) {
    const url = Routing.generate('api_post_cursus_general_parameters')
    return this.$http.post(url, {parameters: params}).then(d => {
      if (d['status'] === 200) {
        return 'success'
      }
    })
  }

  getLocationResources() {
    const url = Routing.generate('api_get_cursus_reservation_resources')
    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }

  getLocations() {
    const url = Routing.generate('api_get_cursus_locations')
    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }
}