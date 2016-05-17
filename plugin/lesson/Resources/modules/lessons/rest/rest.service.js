export default class RestService {

  constructor($http, $q) {
    this.$http = $http
    this.$q = $q
  }

  getTree(lesson) {
    const url = Routing.generate('icap_lesson_api_get_tree', {
      'lesson': lesson
    })
    return this.$http.get(url).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }

  getChapterList(lesson) {
    const url = Routing.generate('icap_lesson_api_get_chapter_list', {
      'lesson': lesson
    })
    return this.$http.get(url).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }

  getDefaultChapter(lesson) {
    const url = Routing.generate('icap_lesson_api_get_default_chapter', {
      'lesson': lesson
    })
    return this.$http.get(url).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }

  getChapter(lesson, slug) {
    const url = Routing.generate('icap_lesson_api_view_chapter', {
      'lesson': lesson,
      'chapter': slug
    })
    return this.$http.get(url).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }

  createChapter(lesson, newChapter) {
    const url = Routing.generate('icap_lesson_api_create_chapter', {
      'lesson': lesson
    })

    // newChapter variable must be in an object under the key 'chapter'
    // in order to be processed as form payload by the Symfony backend
    return this.$http.post(url, {"chapter": newChapter}).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }

  updateChapter(lesson, slug, updatedChapter) {
    const url = Routing.generate('icap_lesson_api_edit_chapter', {
      'lesson': lesson,
      'chapter': slug
    })

    // updatedChapter variable must be in an object under the key 'chapter'
    // in order to be processed as form payload by the Symfony backend
    return this.$http.put(url, {"chapter": updatedChapter}).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }

  copyChapter(lesson, slug, newChapter, copyChildren) {
    const url = Routing.generate('icap_lesson_api_duplicate_chapter', {
      'lesson': lesson,
      'chapter': slug
    })

    // newChapter variable must be in an object under the key 'chapter'
    // in order to be processed as form payload by the Symfony backend
    newChapter.copyChildren = copyChildren
    return this.$http.post(url, {"chapter": newChapter}).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }

  deleteChapter(lesson, slug, deleteChildren) {
    const url = Routing.generate('icap_lesson_api_delete_chapter', {
      'lesson': lesson,
      'chapter': slug,
      'deleteChildren': deleteChildren
    })

    return this.$http.delete(url).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }
  
  moveChapter(lesson, chapter, parent, prevSibling) {
    const url = Routing.generate('icap_lesson_api_move_chapter', {
      'chapter': chapter,
      'lesson': lesson
    })

    const data = {
      'newParent': parent,
      'prevSibling': prevSibling
    }

    return this.$http.patch(url, data).then(
      response => response.data,
      reason => this.$q.reject(reason)
    )
  }
}

RestService.$inject = ['$http', '$q']