let _url = new WeakMap()
let _$resource = new WeakMap()
let _transFilter = new WeakMap()

export default class BibliographyController {

  constructor(url, $resource, Messages, transFilter) {
    _url.set(this, url)
    _$resource.set(this, $resource)
    _transFilter.set(this, transFilter)

    // Public functions
    this.bookReference = {}
    this.searchResults = null
    this.searchFromApi = false
    this.enterDataManually = false
    this.messages = Messages
    this.dismissTimeOut = 5000
    this.currentPage = 1
  }

  search() {
    this.searchFromApi = true
    this.searchResults = null

    const url = _url.get(this)('icap_bibliography_api_book_search', {
      query: this.bookReference.isbn || this.bookReference.title || this.bookReference.author,
      index: this.bookReference.isbn ? 'isbn' : this.bookReference.title ? 'title' : this.bookReference.author ? 'author' : '',
      page: this.currentPage
    })

    let Search = _$resource.get(this)(url)
    let search = Search.get(
      () => {
        this.searchResults = search.data
      },
      () => {
        this._setMessage('danger', 'icap_bibliography_api_unavailable')
        this.endSearch()
      }
    )
  }
  
  getBook(bookId) {
    const url = _url.get(this)('icap_bibliography_api_book_details', {
      bookId: bookId
    })

    let Book = _$resource.get(this)(url)
    let book = Book.get(
      () => {
        this.bookReference = book.data[0]
        this.endSearch()
      },
      () => {
        this._setMessage('danger', 'icap_bibliography_api_book_unavailable')
        this.endSearch()
      }
    )
  }

  endSearch() {
    this.searchFromApi = false
    this.enterDataManually = true
    this.searchResults = null
  }

  enterData() {
    this.enterDataManually = true
  }

  selectBook(book) {
    this.bookReference = book
    this.endSearch()
  }
  
  cancel() {
    this.searchFromApi = this.enterDataManually = false
  }

  _setMessage(type, msg) {
    this.messages.push({
      type: type,
      msg: _transFilter.get(this)(msg, {}, 'icap_bibliography')
    })
  }

  closeMessage(index) {
    this.messages.splice(index, 1)
  }

}

BibliographyController.$inject = [
  'url',
  '$resource',
  'Messages',
  'transFilter'
]
