import {trans} from '#/main/app/intl/translation'

import {UrlCell} from '#/main/app/data/types/url/components/cell'
import {UrlDisplay} from '#/main/app/data/types/url/components/display'
import {UrlInput} from '#/main/app/data/types/url/components/input'

// TODO : add validation

const dataType = {
  name: 'url',
  meta: {
    icon: 'fa fa-fw fa-link',
    label: trans('url', {}, 'data'),
    description: trans('url_desc', {}, 'data')
  },
  components: {
    details: UrlDisplay,
    table: UrlCell,

    // new api
    input: UrlInput,
    display: UrlDisplay,
    cell: UrlCell
  }
}

export {
  dataType
}
