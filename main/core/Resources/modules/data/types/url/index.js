import {trans} from '#/main/core/translation'

import {UrlGroup} from '#/main/core/layout/form/components/group/url-group'
import {UrlDisplay} from '#/main/core/data/types/url/components/display'

const URL_TYPE = 'url'

const urlDefinition = {
  meta: {
    type: URL_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa-link',
    label: trans('url', {}, 'data'),
    description: trans('url_desc', {}, 'data')
  },
  components: {
    details: UrlDisplay,
    form: UrlGroup
  }
}

export {
  URL_TYPE ,
  urlDefinition
}
