import {trans} from '#/main/core/translation'

import {UrlGroup} from '#/main/core/layout/form/components/group/url-group'
import {UrlDisplay} from '#/main/app/data/url/components/display'

const dataType = {
  name: 'url',
  meta: {
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
  dataType
}
