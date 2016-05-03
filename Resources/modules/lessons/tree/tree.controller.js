let _$rootScope = new WeakMap()
let _$scope = new WeakMap()
let _$location = new WeakMap()
let _$q = new WeakMap()
let _restService = new WeakMap()
let _$filter = new WeakMap()

export default class TreeController {
  constructor($rootScope, $scope, $location, $q, lessonData, restService, Chapter, Tree, $filter) {
    _$rootScope.set(this, $rootScope)
    _$scope.set(this, $scope)
    _$location.set(this, $location)
    _$q.set(this, $q)
    _restService.set(this, restService)
    _$filter.set(this, $filter)

    this.lessonData = lessonData
    this.tree = Tree
    this.displayDrag = false
    this.dragEnabled = false
    this.chapter = Chapter

    this.init()
  }

  init() {
    // Construct the tree
    this.tree.refresh(this.lessonData.lessonId)

    // Event listeners
    _$rootScope.get(this).$on('$routeChangeSuccess', (event, current) => {
      // Display or hide the button enabling drag and drop of tree items
      // $$route is a private Angular property but seems the only place where to find the matching route pattern
      this.displayDrag = current.$$route.originalPath == '/:slug'
      // If dragging is enabled when the button must be hidden, deactivate dragging
      if (current.$$route.originalPath != '/:slug') {
        this.dragEnabled = false
      }
    })
  }

  get treeOptions() {
    return {
      beforeDrop: this._dragChapter.bind(this)
    }
  }

  _dragChapter(event) {

    // Which chapter id dragged?
    let chapter = event.source.nodeScope.$modelValue.slug

    // Find its new parent and new previous sibling
    let newParent = 'root-' + this.lessonData.lessonId // By default, consider the root node as the new parent
    let previousSibling = null

    if (event.dest.nodesScope.$parent.$type == 'uiTreeNode') { // The parent is not the root node

      // Find the new parent slug
      newParent = event.dest.nodesScope.$parent.$modelValue.slug

      // If the chapter is not the first child, find its previous sibling
      if (event.dest.index > 0) {
        previousSibling = event.dest.nodesScope.$parent.$modelValue.__children[event.dest.index - 1].slug
      }

    } else { // The parent is the root node
      if (event.dest.index > 0) {
        previousSibling = event.dest.nodesScope.$modelValue[event.dest.index - 1].slug
      }
    }

    return _restService.get(this).moveChapter(
      this.lessonData.lessonId,
        chapter,
        newParent,
        previousSibling
      )
      .then(
        response => {
          // Chapter
          _$rootScope.get(this).$emit('chapterMoved', response.message, response.chapter)
          // OR
          // Update chapter variable and redirect to its page
          this.chapter.refresh(response.chapter)
          _$location.get(this).url('/' + response.chapter.slug)
        }
      )
  }

  getChapterUrl(slug) {
    // Links are only clickable in a not draggable state
    if (!this.dragEnabled) {
      return '#/' + slug
    }
  }

  toggle(element) {
    element.toggle()
  }

  collapseAll() {
    _$scope.get(this).$broadcast('angular-ui-tree:collapse-all');
  }

  expandAll() {
    _$scope.get(this).$broadcast('angular-ui-tree:expand-all');
  }

  getDragEnableButtonText() {
    return !this.dragEnabled
      ? _$filter.get(this)('trans')('enable_move', 'icap_lesson')
      : _$filter.get(this)('trans')('disable_move', 'icap_lesson')
  }

}

TreeController.$inject = [
  '$rootScope',
  '$scope',
  '$location',
  '$q',
  'lesson.data',
  'restService',
  'Chapter',
  'Tree',
  '$filter'
]