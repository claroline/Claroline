/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Translator*/

export default class EntryViewCtrl {
  constructor($state, $stateParams, $filter, NgTableParams, ClacoFormService, EntryService, FieldService, CategoryService, KeywordService, CommentService) {
    this.$state = $state
    this.$filter = $filter
    this.ClacoFormService = ClacoFormService
    this.EntryService = EntryService
    this.FieldService = FieldService
    this.CategoryService = CategoryService
    this.KeywordService = KeywordService
    this.CommentService = CommentService
    this.entryId = parseInt($stateParams.entryId)
    this.entry = {}
    this.userId = ClacoFormService.getUserId()
    this.title= ClacoFormService.getResourceNodeName()
    this.config = ClacoFormService.getResourceDetails()
    this.template = ClacoFormService.getTemplate()
    this.fields = FieldService.getFields()
    this.tinymceOptions = ClacoFormService.getTinymceConfiguration()
    this.categories = []
    this.keywords = []
    this.collapsed = {keywords: true, categories: true, comments: true}
    this.comments = CommentService.getComments()
    this.newComment = null
    this.newCommentFormActive = false
    this.tinymceOptions = ClacoFormService.getTinymceConfiguration()
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.comments}
    )
    this.commentsEditionForm = {}
    this.metadataAllowed = ClacoFormService.getCanEdit() || this.config['display_metadata'] === 'all'
    this._addCommentCallback = this._addCommentCallback.bind(this)
    this._updateCommentCallback = this._updateCommentCallback.bind(this)
    this._removeCommentCallback = this._removeCommentCallback.bind(this)
    this._updateEntryCallback = this._updateEntryCallback.bind(this)
    this._removeEntryCallback = this._removeEntryCallback.bind(this)
    this.initialize()
  }

  _addCommentCallback(data) {
    this.CommentService._addCommentCallback(data)
    this.newCommentFormActive = false
    this.newComment = null
    this.tableParams.reload()
  }

  _updateCommentCallback(data) {
    this.CommentService._updateCommentCallback(data)
    this.tableParams.reload()
  }

  _removeCommentCallback(data) {
    this.CommentService._removeCommentCallback(data)
    this.tableParams.reload()
  }

  _updateEntryCallback(data) {
    this.EntryService._updateEntryCallback(data)
    const e = JSON.parse(data)
    this.entry['status'] = e['status']
  }

  _removeEntryCallback(data) {
    this.EntryService._removeEntryCallback(data)
    this.$state.go('entries_list')
  }

  initialize() {
    this.ClacoFormService.clearMessages()
    this.entry = this.EntryService.getEntry(this.entryId)
    this.collapsed['keywords'] = !this.config['open_keywords']
    this.collapsed['categories'] = !this.config['open_categories']
    this.collapsed['comments'] = !this.config['open_comments']

    if (this.entry === undefined) {
      this.EntryService.getEntryById(this.entryId).then(d => {
        this.entry = d
        this.initializeCategories()
        this.initializeKeywords()
        this.initializeTemplate()
      })
    } else {
      this.initializeCategories()
      this.initializeKeywords()
      this.initializeTemplate()
    }
    this.CommentService.initializeComments(this.entryId)
  }

  initializeTemplate() {
    if (this.template) {
      this.template = this.template.replace('%clacoform_entry_title%', this.entry['title'])
      this.fields.forEach(f => {
        const name = f['name']
        const id = f['id']
        let replacedField = ''

        if (this.metadataAllowed || !f['isMetadata']) {
          switch (f['type']) {
            case 3 :
              replacedField = this.$filter('date')(this.entry[id], 'dd/MM/yyyy')
              break
            case 6 :
              replacedField = this.entry[id].join(', ')
              break
            case 7 :
              replacedField = this.FieldService.getCountryNameFromCode(this.entry[id])
              break
            default :
              replacedField = this.entry[id]
          }
        }
        if (replacedField === undefined) {
          replacedField = ''
        }
        this.template = this.template.replace(`%${this.ClacoFormService.removeAccent(name)}%`, replacedField)
      })
    }
  }

  initializeCategories() {
    this.entry['categories'].forEach(c => {
      this.categories.push(c)

      if (!this.metadataAllowed && this.config['display_metadata'] === 'manager') {
        const managers = c['managers']
        managers.forEach(m => {
          if (m['id'] === this.userId) {
            this.metadataAllowed = true
          }
        })
      }
    })
  }

  initializeKeywords() {
    this.entry['keywords'].forEach(k => this.keywords.push(k['name']))
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  isAllowed() {
    return this.EntryService.getCanOpenEntry(this.entryId)
  }

  toggleCollapsed(type) {
    this.collapsed[type] = !this.collapsed[type]
  }

  isCommentsDisplayable() {
    const isAnon = this.ClacoFormService.getIsAnon()

    return this.config['display_comments'] || (this.config['comments_enabled'] && (!isAnon || this.config['anonymous_comments_enabled']))
  }

  isCommentsEnabled() {
    const isAnon = this.ClacoFormService.getIsAnon()

    return this.config['comments_enabled'] && (!isAnon || this.config['anonymous_comments_enabled'])
  }

  canManageComments() {
    return this.canEdit() || this.EntryService.isManagerEntry(this.entryId)
  }

  canEditComment(comment) {
    return this.canManageComments() || (comment['user'] && comment['user']['id'] === this.userId)
  }

  displayNewCommentForm() {
    this.newCommentFormActive = true
  }

  hideNewCommentForm() {
    this.newCommentFormActive = false
    this.newComment = null
  }

  createNewComment() {
    this.CommentService.createComment(this.entryId, this.newComment, this._addCommentCallback)
  }

  activateCommentEdition(commentId) {
    this.commentsEditionForm[commentId] = true
  }

  closeCommentEditionForm(commentId) {
    this.commentsEditionForm[commentId] = false
  }

  editComment(comment) {
    this.CommentService.editComment(comment, this._updateCommentCallback)
    this.commentsEditionForm[comment['id']] = false
  }

  activateComment(comment) {
    this.CommentService.activateComment(comment, this._updateCommentCallback)
  }

  blockComment(comment) {
    this.CommentService.blockComment(comment, this._updateCommentCallback)
  }

  deleteComment(comment) {
    this.CommentService.deleteComment(comment, this._removeCommentCallback)
  }

  canEditEntry() {
    return this.EntryService.getCanEditEntry(this.entryId)
  }

  canManageEntry() {
    return this.EntryService.getCanManageEntry(this.entryId)
  }

  canCreate() {
    return this.ClacoFormService.getCanCreateEntry()
  }

  canSearch() {
    return this.ClacoFormService.getCanSearchEntry()
  }

  deleteEntry() {
    this.EntryService.deleteEntry(this.entry, this._removeEntryCallback)
  }

  changeEntryStatus() {
    this.EntryService.changeEntryStatus(this.entry, this._updateEntryCallback)
  }

  getRandomEntry() {
    this.ClacoFormService.getRandomEntryId(this.ClacoFormService.getResourceId()).then(d => {
      if (d) {
        if ((typeof d === 'number') && (d > 0)) {
          this.$state.go('entry_view', {entryId: d})
        } else {
          this.ClacoFormService.setErrorMessage(Translator.trans('no_available_random_entry', {}, 'clacoform'))
        }
      }
    })
  }

  getErrorMessage() {
    return this.ClacoFormService.getErrorMessage()
  }

  clearErrorMessage() {
    this.ClacoFormService.clearErrorMessage()
  }

  filterCategory(categoryName) {
    this.EntryService.setCategoryFilter(categoryName)
    this.$state.go('entries_list')
  }

  filterKeyword(keyword) {
    this.EntryService.setKeywordFilter(keyword)
    this.$state.go('entries_list')
  }

  getCountryName(code) {
    return this.FieldService.getCountryNameFromCode(code)
  }
}