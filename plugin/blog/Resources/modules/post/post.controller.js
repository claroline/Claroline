import angular from 'angular/index'
import confirmDeletionTemplate from '../post/confirmDeletion.partial.html'
import confirmCommentDeletionTemplate from '../post/confirmCommentDeletion.partial.html'

let _url = new WeakMap()
let _$routeParams = new WeakMap()
let _$location = new WeakMap()
let _Messages = new WeakMap()
let _transFilter = new WeakMap()
let _modalInstance = new WeakMap()
let _modalFactory = new WeakMap()
let _$scope = new WeakMap()
let _$rootScope = new WeakMap()
let _$anchorScroll = new WeakMap()

export default class PostController {

  constructor(blogService, url, $routeParams, $location, Messages, transFilter, modal, $scope, tinyMceConfig, $rootScope, $anchorScroll) {

    _url.set(this, url)
    _$routeParams.set(this, $routeParams)
    _$location.set(this, $location)
    _Messages.set(this, Messages)
    _transFilter.set(this, transFilter)
    _modalInstance.set(this, null)
    _modalFactory.set(this, modal)
    _$scope.set(this, $scope)
    _$rootScope.set(this, $rootScope)
    _$anchorScroll.set(this, $anchorScroll)

    this.blog = blogService
    this.tinymceOptions = tinyMceConfig
    this.datepicker = {opened: false}
    this.locale = document.documentElement.lang
    this.datepickerFormats = {
      'en': 'MM-dd-yyyy',
      'fr': 'dd/MM/yyyy'
    }
    this.firstDayInWeek = {
      'en': 0,
      'fr': 1
    }
    this.datePickerOptions = {
      'starting-day': this.firstDayInWeek[this.locale]
    }

    this.disableButtons = false
    this.newCommentMessage = ''

    this.init()

  }

  init() {
    // If post is displayed, always fetch its newest version from API if the router asks for it
    if (_$routeParams.get(this).loadPost && _$routeParams.get(this).slug !== undefined) {
      this.blog.setCurrentPostBySlug(_$routeParams.get(this).slug)
        .then(
          () => {
            
          },
          () => {
            this._setMessage('danger', 'error_404', {}, false, 'icap_blog', true)
            _$location.get(this).url('/')
          }
        )
    }
    
    // Create a new post on controller init with empty tag array and default publication date
    this.blog.newPost = {
      tags: [],
      publication_date: new Date()
    }
  }
  
  getPostUrl(slug) {
    return _url.get(this)('icap_blog_post_view', {
      blogId: this.blog.id,
      postSlug: slug
    })
  }

  getAuthenticationUrl() {
    return _url.get(this)('')
  }

  getTags($query) {
    return this.blog.tags.filter(element => element.text.includes($query))
  }

  getErrorMessage($errors) {
    let errorMessage = ''
    if ('required' in $errors) {
      errorMessage = _transFilter.get(this)('required_field', {}, 'icap_blog')
    }
    if ('maxlength' in $errors) {
      errorMessage = _transFilter.get(this)('maxlength_field', {}, 'icap_blog')
    }
    return errorMessage
  }

  filterByTag(tag) {
    _$location.get(this).url(`/tag/${tag.slug}`)
  }

  openDatePicker() {
    this.datepicker.opened = true
  }

  cancelEdit() {
    _$location.get(this).url('/')
  }
  
  cancelCreate() {
    _$location.get(this).url('/')
  }

  editPost(post) {
    this.blog.setCurrentPost(post)
    _$location.get(this).url('/' + post.slug + '/edit')
  }

  togglePostVisibility(post) {
    post.is_request_pending = true
    this.blog.togglePostVisibility(post).then(
      () => {
        if (post.is_published) {
          this._setMessage('success', 'icap_blog_post_publish_success')
        } else {
          this._setMessage('success', 'icap_blog_post_unpublish_success')
        }
        _$rootScope.get(this).$emit('post_visibility_toggled')
      },
      () => {
        if (post.is_published) {
          this._setMessage('danger', 'icap_blog_post_unpublish_error')
        } else {
          this._setMessage('danger', 'icap_blog_post_publish_error')
        }
      }
    )
      .finally(
        () => {
          post.is_request_pending = false
        }
      )
  }

  toggleCommentVisibility(comment, post) {
    comment.is_request_pending = true
    this.blog.toggleCommentVisibility(comment, post)
      .then(
        () => {
          comment.is_published ?
            this._setMessage('success', 'icap_blog_comment_publish_success')
          : this._setMessage('success', 'icap_blog_comment_unpublish_success')
          
        },
        () => {
          comment.is_published ?
            this._setMessage('danger', 'icap_blog_comment_publish_error')
          : this._setMessage('danger', 'icap_blog_comment_unpublish_error')
        }
      )
      .finally(
        () => {
          comment.is_request_pending = false
        }
      )
  }

  countComments(post) {
    return post.comments.filter(elem => elem.is_published).length
  }
  
  createPost() {
    //Disable buttons
    this.disableButtons = true
    this.blog.createPost()
      .then(
        success => {
          this._setMessage('success', 'icap_blog_post_add_success', {}, false, 'icap_blog', true)

          if (!this.blog.options.auto_publish_post && !this.blog.isGrantedAdmin) {
            this._setMessage('success', 'icap_blog_post_need_validation_before_publishing', {}, false, 'icap_blog', true)
          }
          _$location.get(this).url('/' + success.slug)
          _$location.get(this).hash('top')
          _$anchorScroll.get(this)()

          _$rootScope.get(this).$emit('post_created')
        },
        () => {
          this._setMessage('danger', 'icap_blog_post_add_error')
        }
      )
      .finally(
        () => {
          // Re-enable buttons
          this.disableButtons = false
        }
      )
  }
  
  updatePost() {
    // Disable buttons
    this.disableButtons = true
    this.blog.editPost()
      .then(
        () => {
          this._setMessage('success', 'icap_blog_post_edit_success', {}, false, 'icap_blog', true)
          if (!this.blog.options.auto_publish_post && !this.blog.isGrantedAdmin) {
            this._setMessage('success', 'icap_blog_post_need_validation_before_publishing', {}, false, 'icap_blog', true)
          }
          _$location.get(this).url('/' + this.blog.currentPost.slug)

          _$location.get(this).hash('top')
          _$anchorScroll.get(this)()
        },
        () => {
          this._setMessage('danger', 'icap_blog_post_edit_error')
        }
      )
      .finally(
        () => {
          // Re-enable buttons
          this.disableButtons = false
        }
      )
  }

  confirmDeletePost(post) {
    this.postToDelete = post
    this._modal(confirmDeletionTemplate)
  }

  deletePost() {
    // Disable buttons
    this.disableButtons = true

    this.blog.deletePost(this.postToDelete, this.currentPage)
      .then(
        () => {
          this._setMessage('success', 'icap_blog_post_delete_success')
          _$location.get(this).url('/')
          _$rootScope.get(this).$emit('post_deleted')
        },
        () => {
          this._setMessage('danger', 'icap_blog_post_delete_error')
        }
      )
      .finally(
        () => {
          this._cancelModal()
          // Re-enable buttons
          this.disableButtons = false
        }
      )
  }

  addComment(post, message, form) {
    this.disableButtons = true
    this.blog.addComment(post, message)
      .then(
        () => {
          this._setMessage('success', 'icap_blog_comment_add_success')

          if (!this.blog.options.auto_publish_comment && !this.blog.isGrantedAdmin) {
            this._setMessage('success', 'icap_blog_post_comment_need_validation_before_publishing')
          }
          this.newCommentMessage = ''
          form.$setPristine()
        },
        () => {
          this._setMessage('danger', 'icap_blog_comment_add_error')
        }
      )
      .finally(
        () => {
          this.disableButtons = false
        }
      )
  }

  prepareCommentEdition(comment) {
    comment.tempData = angular.copy(comment)
    comment.in_edition = true
  }

  cancelEditComment(comment) {
    comment.in_edition = false
    delete comment.tempData
  }

  editComment(post, comment) {
    this.disableButtons = true
    this.blog.editComment(post, comment)
      .then(
        () => {
          this._setMessage('success', 'icap_blog_comment_edit_success')
        },
        () => {
          this._setMessage('danger', 'icap_blog_comment_edit_error')
        }
      )
      .finally(
        () => {
          this.disableButtons = false
          this.cancelEditComment(comment)
        }
      )
  }

  confirmDeleteComment(comment) {
    this.commentToDelete = comment
    this._modal(confirmCommentDeletionTemplate)
  }

  deleteComment() {
    // Disable buttons
    this.disableButtons = true

    this.blog.deleteComment(this.commentToDelete, this.blog.currentPost)
      .then(
        () => {
          this._setMessage('success', 'icap_blog_comment_delete_success')
        },
        () => {
          this._setMessage('danger', 'icap_blog_comment_delete_error')
        }
      )
      .finally(
        () => {
          this._cancelModal()
          // Re-enable buttons
          this.disableButtons = false
        }
      )
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

  isPublishable(post) {
    return  new Date(post.publication_date) <= new Date()
  }

}

PostController.$inject = [
  'blogService',
  'url',
  '$routeParams',
  '$location',
  'Messages',
  'transFilter',
  'blogModal',
  '$scope',
  'tinyMceConfig',
  '$rootScope',
  '$anchorScroll'
]