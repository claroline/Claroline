import angular from 'angular/index'

let _$location = new WeakMap()
let _$anchorScroll = new WeakMap()
let _Messages = new WeakMap()
let _transFilter = new WeakMap()
let _modalInstance = new WeakMap()
let _modalFactory = new WeakMap()
let _$scope = new WeakMap()
let _$routeParams = new WeakMap()

export default class BlogController {
  constructor(blogService, $location, $anchorScroll, Messages, transFilter, modal, $scope, $routeParams, tinyMceConfig) {

    _$location.set(this, $location)
    _$anchorScroll.set(this, $anchorScroll)
    _Messages.set(this, Messages)
    _transFilter.set(this, transFilter)
    _modalInstance.set(this, null)
    _modalFactory.set(this, modal)
    _$scope.set(this, $scope)
    _$routeParams.set(this, $routeParams)

    this.blog = blogService
    this.tinymceOptions = tinyMceConfig
    this.disableButtons = false
    this.postToDelete = null
    this.currentPage = 1
    this.postsPerPageValues = [
      5, 10, 20
    ]

    this.init()
    
  }
  
  init() {
    // Store a copy of the options and panel disposition
    this.blog.optionsCopy = angular.copy(this.blog.options)
    this.blog.panelsCopy = angular.copy(this.blog.panels)

    this.blog.getInfo()
    this.loadPosts(this.currentPage)
  }

  pageChanged() {
    this.loadPosts(this.currentPage)
  }

  loadPosts(page) {
    // Get posts for the view if blog is not filtered
    if (!_$routeParams.get(this).is_filter) {

      this.blog.getPosts(page)

    } else { // Get a filtered list of posts

      switch (_$routeParams.get(this).filter) {

        case 'tag':
          this.blog.getPostsByTag(_$routeParams.get(this).slug, page)
            .then(
              success => {
                this._setMessage('info', 'post_filtered_by_tag', {tagName: success.tag.text}, true)
              }
            )
          break

        case 'search':
          this.blog.getPostsBySearch(_$routeParams.get(this).terms, page)
            .then(
              () => {
                this._setMessage('info', 'post_filtered_by_search', {searchTerms: decodeURI(_$routeParams.get(this).terms)}, true, 'icap_blog', true)
              }
            )

          this.blog.search(this.searchTerms)
          break
        
        case 'author':
          this.blog.getPostsByAuthor(_$routeParams.get(this).authorId, page)
            .then(
              success => {
                this._setMessage('info', 'post_filtered_by_author', {authorName: `${success.author.firstName} ${success.author.lastName}`}, true)
              }
            )
          break
        
        case 'date':
          if (_$routeParams.get(this).day) {
            this.blog.getPostsByDay(_$routeParams.get(this).year, _$routeParams.get(this).month, _$routeParams.get(this).day, page)
              .then(
                () => {
                  this._setMessage('info', 'post_filtered_by_day', {'date': `${_$routeParams.get(this).day}/${_$routeParams.get(this).month}/${_$routeParams.get(this).year}`}, true)
                }
              )
          } else {
            this.blog.getPostsByMonth(_$routeParams.get(this).year, _$routeParams.get(this).month, page)
              .then(
                () => {
                  this._setMessage('info', 'post_filtered_by_month', {'date': `${_$routeParams.get(this).month}/${_$routeParams.get(this).year}`}, true)
                }
              )
          }
          break
          
        default:
          break
      }
    }
  }

  getPostUrl(slug) {
    return `#/${slug}`
  }

  displayOptions() {
    _$location.get(this).url('/configure')
  }

  cancelConfigure() {
    delete this.blog.optionsCopy
    delete this.blog.panelsCopy
    _$location.get(this).url('/')
  }
  
  saveConfigure() {
    // Convert panel tree to a string representing the widget bar configuration

    let listWidgetBlog = ''
    this.blog.panelsCopy.forEach(element => {
      listWidgetBlog += element.id.toString() + element.visibility.toString()
    })
    this.blog.optionsCopy.list_widget_blog = listWidgetBlog
    
    this.disableButtons = true
    this.blog.editOptions()
      .then(
        () => {
          this.cancelConfigure()
          this._setMessage('success', 'icap_blog_post_configure_success', {}, false, 'icap_blog', true)
        },
        () => {
          this._setMessage('danger', 'icap_blog_post_configure_error')
        }
      )
      .finally(
        () => {
          this.disableButtons = false
        }
      )
    
  }

  displayPost(post, goToComments) {
    this.blog.currentPost = post
    let commentsHash = goToComments ? '#comments' : ''
    _$location.get(this).url('/' + post.slug + commentsHash)
  }

  togglePanel(panel) {
    panel.visibility = + !panel.visibility // cast to integer (with + operator) and toggle at the same time
  }
  
  editInfo() {
    // Disable button
    this.disableButtons = true
    
    this.blog.editInfo(this.blog.tempInfo)
      .then(
        () => {
          this._setMessage('success', 'icap_blog_edit_infos_success', {}, false, 'icap_blog', true)
          _$location.get(this).url('/')
        },
        () => {
          this._setMessage('danger', 'icap_blog_edit_infos_error',  {}, false, 'icap_blog', true)
        }
      )
      .finally(() => {
        // Re-enable buttons
        this.disableButtons = false
      })
  }
  
  cancelInfo() {
    _$location.get(this).url('/')
  }

  _setMessage(type, msg, params = {}, filter = false, realm = 'icap_blog', keep = false) {
    _Messages.get(this).push({
      type: type,
      msg: _transFilter.get(this)(msg, params, realm),
      filter: filter,
      keep: keep
    })
  }

  _modal(template) {
    _modalInstance.set(this, _modalFactory.get(this).open(template, _$scope.get(this)))
  }

  _cancelModal() {
    _modalInstance.get(this).dismiss()
  }

}

BlogController.$inject = [
  'blogService',
  '$location',
  '$anchorScroll',
  'Messages',
  'transFilter',
  'blogModal',
  '$scope',
  '$routeParams',
  'tinyMceConfig'
]