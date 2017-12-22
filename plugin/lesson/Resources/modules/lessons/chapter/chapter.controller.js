import confirmDeletionTemplate from './confirmDeletion.partial.html'
import angular from 'angular/index'

let _$rootScope = new WeakMap()
let _$scope = new WeakMap()
let _modalInstance = new WeakMap()
let _modalFactory = new WeakMap()
let _$route = new WeakMap()
let _$location = new WeakMap()
let _$anchorScroll = new WeakMap()
let _restService = new WeakMap()
let _transFilter = new WeakMap()

export default class ChapterController {

  constructor($rootScope, $scope, lessonData, modal, $route, $location, $anchorScroll, restService, Chapter, Tree, Alerts, tinyMceConfig, transFilter) {
    _$rootScope.set(this, $rootScope)
    _$scope.set(this, $scope)
    _$route.set(this, $route)
    _$location.set(this, $location)
    _$anchorScroll.set(this, $anchorScroll)
    _modalInstance.set(this, null)
    _modalFactory.set(this, modal)
    _restService.set(this, restService)
    _transFilter.set(this, transFilter)

    // lessonData contains the default values for the lesson
    this.lessonData = lessonData
    this.isGranted = lessonData.isGranted
    this.tinymceOptions = tinyMceConfig

    this.chapter = Chapter
    this.tree = Tree
    this.alerts = Alerts

    this.chapters = []
    this._createdChapter = {}
    this._deletedChapter = {}
    this._editedChapter = {}

    // Form sumbit button state
    this.buttonSubmit = {disabled: false}

    this.init()
  }

  init() {
    // Intialize the chapter only if there is one in the lesson
    if (this.lessonData.defaultChapter) {
      // If a LESSON is displayed, there's no chapter slug in the route (route: '/')
      // We need to rely on a default chapter slug
      let chapterToLoad = _$route.get(this).current.pathParams.slug
        ? _$route.get(this).current.pathParams.slug
        : this.lessonData.defaultChapter

      // Future created chapter will have the current chapter as default parent if a slug is in the url, or root
      this._createdChapter.parent = _$route.get(this).current.pathParams.slug
        ? chapterToLoad
        : this.lessonData.root

      // We have to provide all the tree in a flattened array
      this._getChapterList(this.lessonData.lessonId)

      // If the chapter isn't loaded, we have to fetch the data
      if (chapterToLoad != this.chapter.current.slug) {
        _restService.get(this).getChapter(
          this.lessonData.lessonId,
          chapterToLoad
        )
          .then(
            response => {
              this.chapter.refresh(response)
            }
        )
      }
    }
  }


  newChapter(slug) {
    _$location.get(this).url('/' + slug + '/new')
  }

  updateChapter(slug) {
    _$location.get(this).url('/' + slug + '/edit')
  }

  moveChapter(slug) {
    _$location.get(this).url('/' + slug + '/move')
  }

  duplicateChapter(slug) {
    _$location.get(this).url('/' + slug + '/duplicate')
  }

  getPreviousChapterUrl() {
    return this.chapter.current.previous
      ? '#/' + this.chapter.current.previous
      : null
  }

  getNextChapterUrl() {
    return this.chapter.current.next
      ? '#/' + this.chapter.current.next
      : null
  }

  createFirstChapter() {
    this.lessonData.createNew = true
    _$location.get(this).url('/new')
  }

  createChapter(form) {
    if (form.$valid) {
      // Disable submit button while XHR
      this.buttonSubmit.disabled = true

      _restService.get(this).createChapter(
        this.lessonData.lessonId,
        this._createdChapter
      )
        .then(
          response => {
            // First chapter of the lesson has already been created (this time or during a previous submission)
            this.lessonData.createNew = false
            this.chapter.refresh(response.chapter)

            this.tree.refresh(this.lessonData.lessonId)
            this._getDefaultChapter(this.lessonData.lessonId)

            this.alerts.push({'type': 'success', 'msg': response.message})

            form.$setPristine()

            _$location.get(this).url('/' + response.chapter.slug)
          },
          () => {
            // Re-enable the submit button and display errors ?
            this.buttonSubmit.disabled = false
          }
      )
    } else {
      this.alerts.push({'type': 'danger', 'msg': _transFilter.get(this)('form_error', {}, 'icap_lesson')})
    }
  }

  editChapter(form) {
    if (form.$valid) {
      // Disable submit button
      this.buttonSubmit.disabled = true

      _restService.get(this).updateChapter(
        this.lessonData.lessonId,
        this.chapter.current.slug,
        this.chapter.edited
      )
        .then(
          response => {
            this.chapter.refresh(response.chapter)
            this.tree.refresh(this.lessonData.lessonId)
            this.alerts.push({'type': 'success', 'msg': response.message})
            form.$setPristine()
            _$location.get(this).url('/' + response.chapter.slug)
          },
          () => {
            // Re-enable the submit button and display errors ?
            this.buttonSubmit.disabled = false
          }
      )
    } else {
      this.alerts.push({'type': 'danger', 'msg': _transFilter.get(this)('form_error', {}, 'icap_lesson')})
    }
  }

  copyChapter(form) {
    if (form.$valid) {
      // Disable submit button
      this.buttonSubmit.disabled = true

      // Check if the checkbox exists and is checked
      let copyChildren = !!(form.copyChildren && form.copyChildren.$modelValue)

      _restService.get(this).copyChapter(
        this.lessonData.lessonId,
        this.chapter.current.slug,
        this.chapter.current,
        copyChildren
      )
        .then(
          response => {
            this.chapter.refresh(response.chapter)
            this.tree.refresh(this.lessonData.lessonId)
            this._getDefaultChapter(this.lessonData.lessonId)
            this.alerts.push({'type': 'success', 'msg': response.message})
            _$location.get(this).url('/' + response.chapter.slug)
          },
          () => {
            // Re-enable the submit button and display errors ?
            this.buttonSubmit.disabled = false
          }
      )
    } else {
      this.alerts.push({'type': 'danger', 'msg': _transFilter.get(this)('form_error', {}, 'icap_lesson')})
    }
  }

  confirmDeleteChapter() {
    // this._deletedChapter = this.chapter.current
    this._modal(confirmDeletionTemplate)
  }

  deleteChapter(form) {
    // Disable submit button
    this.buttonSubmit.disabled = true

    // Check if the checkbox exists and is checked
    let deleteChildren = !!(form.deleteChildren && form.deleteChildren.$modelValue)

    _restService.get(this).deleteChapter(
      this.lessonData.lessonId,
      this.chapter.current.slug,
      deleteChildren
    )
      .then(
        response => {
          this.tree.refresh(this.lessonData.lessonId)
          this.alerts.push({'type': 'success', 'msg': response.message})
          this.cancelModal()
          this._getDefaultChapter(this.lessonData.lessonId).then(
            () => _$location.get(this).url('/')
          )
        },
        () => {
          // Re-enable the submit button and display errors ?
          this.buttonSubmit.disabled = false
        }
    )
  }

  goToAnchor(anchor) {
    let $location = _$location.get(this)
    let $anchorScroll = _$anchorScroll.get(this)
    if ($location.hash() !== anchor) {
      $location.hash(anchor)
    } else {
      $anchorScroll()
    }
  }

  _moveChapter(form) {
    // Disable submit button
    this.buttonSubmit.disabled = true

    // Compute the new parent and the previous sibling
    let parentAsSibling = !!(form.asSibling && form.asSibling.$modelValue)

    let newParent = parentAsSibling
      ? null
      : form.parent.$modelValue

    let prevSibling = parentAsSibling
      ? form.parent.$modelValue
      : null

    _restService.get(this).moveChapter(
      this.lessonData.lessonId,
      this.chapter.current.slug,
      newParent,
      prevSibling
    )
      .then(
        response => {
          this.tree.refresh(this.lessonData.lessonId)

          this.alerts.push({'type': 'success', 'msg': response.message})
          _$location.get(this).url('/' + response.chapter.slug)
        },
        () => {
          // Re-enable the submit button and display errors ?
          this.buttonSubmit.disabled = false
        }
    )
  }

  _getChapterList(lesson) {
    return _restService.get(this).getChapterList(lesson).then(
      response => {
        // Find the root chapter and adjust its title
        angular.forEach(response, (value) => {
          if (value.slug == this.lessonData.root) {
            value.title = _transFilter.get(this)('Root', {}, 'icap_lesson')
            this.break
          }
        })
        this.lessonData.chapters = response
        this.lessonData.isEmpty = response.length - 1 === 0
      }
    )
  }

  _getDefaultChapter(lesson) {
    return _restService.get(this).getDefaultChapter(lesson).then(
      response => {
        if ('defaultChapter' in response) {
          this.lessonData.defaultChapter = response.defaultChapter
          this.lessonData.isEmpty = false
        } else {
          this.lessonData.defaultChapter = null
          this.lessonData.isEmpty = true
        }
      }
    )
  }

  cancelForm(slug) {
    let url = !angular.isDefined(slug)
      ? '/'
      : '/' + slug

    this.lessonData.createNew = false
    _$location.get(this).url(url)
  }

  cancelModal(form) {
    if (form) {
      this._resetForm(form)
    }

    _modalInstance.get(this).dismiss()
  }

  _modal(template) {
    _modalInstance.set(this, _modalFactory.get(this).open(template, _$scope.get(this)))
  }
}

ChapterController.$inject = [
  '$rootScope',
  '$scope',
  'lesson.data',
  'lessonModal',
  '$route',
  '$location',
  '$anchorScroll',
  'restService',
  'Chapter',
  'Tree',
  'Alerts',
  'tinyMceConfig',
  'transFilter'
]
