export class SearchListController {
  constructor() {
    this.max = 50
    this.maxChoices = [ 20, 50, 100, 200, 500]
  }

  $onInit() {
    this.page = 1
    this.direction = 'ASC'
    this.query = ''
    this.actions = this.actions || []
  }

  get pages() {
    return Math.ceil(this.totalItems/this.max)
  }

  get search() {
    return {
      'page': Math.min(this.page, this.pages),
      'max': this.max,
      'orderBy': this.orderBy,
      'direction': this.direction,
      'query': this.query
    }
  }

  getItems() {
    this.onChange({'$search': this.search})
  }
}