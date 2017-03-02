import angular from 'angular/index'

let _transFilter = new WeakMap()
let _$location = new WeakMap()
let _Messages = new WeakMap()
let _$rootScope = new WeakMap()
let _$scope = new WeakMap()

export default class BlogPanelController {

  constructor(blogService, transFilter, $location, Messages, $rootScope, $scope) {
    // Private variables
    _transFilter.set(this, transFilter)
    _$location.set(this, $location)
    _Messages.set(this, Messages)
    _$rootScope.set(this, $rootScope)
    _$scope.set(this, $scope)

    // Variables exposed in view
    this.blog = blogService
    this.searchTerms = ''

    this.uiCalendarConfig = {
      editable: false,
      eventClick: (event) => {
        _$location.get(this).url(`/archives/${event.angularParams}`)
      },
      buttonText: {
        today: this._t('today')
      },
      monthNames: [
        this._t('month.january'),
        this._t('month.february'),
        this._t('month.march'),
        this._t('month.april'),
        this._t('month.may'),
        this._t('month.june'),
        this._t('month.july'),
        this._t('month.august'),
        this._t('month.september'),
        this._t('month.october'),
        this._t('month.november'),
        this._t('month.december')
      ],
      monthNamesShort: [
        this._t('month.jan'),
        this._t('month.feb'),
        this._t('month.mar'),
        this._t('month.apr'),
        this._t('month.may'),
        this._t('month.ju'),
        this._t('month.jul'),
        this._t('month.aug'),
        this._t('month.sept'),
        this._t('month.nov'),
        this._t('month.dec')],
      dayNames: [
        this._t('day.sunday'),
        this._t('day.monday'),
        this._t('day.tuesday'),
        this._t('day.wednesday'),
        this._t('day.thursday'),
        this._t('day.friday'),
        this._t('day.saturday')
      ],
      dayNamesShort: [
        this._t('day.sun'),
        this._t('day.mon'),
        this._t('day.tue'),
        this._t('day.wed'),
        this._t('day.thu'),
        this._t('day.fri'),
        this._t('day.sat')
      ],
      today: this._t('today'),
      locale: window.Claroline.Home.locale
    }

    this.init()
  }

  init() {
    _$rootScope.get(this).$on('post_visibility_toggled', this.refetchCalendarEvents)
    _$rootScope.get(this).$on('post_created', this.refetchCalendarEvents)
    _$rootScope.get(this).$on('post_deleted', this.refetchCalendarEvents)
  }

  refetchCalendarEvents() {
    angular.element('#calendar').fullCalendar('refetchEvents')
  }

  getPanelUrl(nameTemplate) {
    return `${nameTemplate}.panel.html`
  }

  filterByTag(tag) {
    _$location.get(this).url(`/tag/${tag.slug}`)
  }

  filterByAuthor(author) {
    _$location.get(this).url(`/author/${author.id}`)
  }

  filterByCalendar(event) {
    _$location.get(this).url(`/archives/${event.angularParams}`)
  }

  filterByArchives(params) {
    _$location.get(this).url('/archives/' + params)
  }

  editInfo() {
    this.blog.tempInfo = angular.copy(this.blog.info)
    _$location.get(this).url('/edit')
  }

  search() {
    _$location.get(this).url('/search/' + encodeURI(this.searchTerms))
  }

  

  toggle(element) {
    element.toggle()
  }

  _t(msg) {
    return _transFilter.get(this)(msg, {}, 'agenda')
  }

  _setMessage(type, msg, params = {}, filter = false, realm = 'icap_blog', keep = false) {
    _Messages.get(this).push({
      type: type,
      msg: _transFilter.get(this)(msg, params, realm),
      filter: filter,
      keep: keep
    })
  }

}

BlogPanelController.$inject = [
  'blogService',
  'transFilter',
  '$location',
  'Messages',
  '$rootScope',
  '$scope'
]