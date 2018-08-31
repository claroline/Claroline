import {trans} from '#/main/core/translation'
import {chain, string, url} from '#/main/core/validation'

import {UrlGroup} from '#/main/core/layout/form/components/group/url-group'
import {UrlDisplay} from '#/main/app/data/url/components/display'

const dataType = {
  name: 'url',
  meta: {
    icon: 'fa fa-fw fa-link',
    label: trans('url', {}, 'data'),
    description: trans('url_desc', {}, 'data')
  },
  validate: (value, options) => chain(value, options, [string, url]),
  components: {
    details: UrlDisplay,
    form: UrlGroup
  }
}

export {
  dataType
}
