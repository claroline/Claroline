import {UrlTab} from '#/plugin/url/home/url/components/tab'
import {UrlTabParameters} from '#/plugin/url/home/url/components/parameters'

export default {
  name: 'url',
  icon: 'fa fa-fw fa-link',
  class: 'HeVinci\\UrlBundle\\Entity\\Home\\UrlTab',
  component: UrlTab,
  parameters: UrlTabParameters
}
