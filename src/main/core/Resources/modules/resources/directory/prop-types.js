import {PropTypes as T} from 'prop-types'

import {constants as listConstants} from '#/main/app/content/list/constants'
import {ListParameters} from '#/main/app/content/list/parameters/prop-types'

const Directory = {
  propTypes: {
    list: T.shape(
      ListParameters.propTypes
    )
  },
  defaultProps: {
    list: Object.assign({}, ListParameters.defaultProps, {
      count: true,
      actions: true,
      display: listConstants.DISPLAY_TILES_SM,
      sorting: 'name',
      availableSort: [
        'name',
        'meta.type',
        'parent',
        'meta.published',
        'meta.created',
        'meta.updated'
      ],
      availableFilters: [
        'name',
        'meta.type',
        'parent',
        'meta.published',
        'meta.created',
        'meta.updated'
      ],
      columns: [
        'name',
        'meta.type',
        'meta.updated'
      ],
      availableColumns: [
        'name',
        'meta.type',
        'parent',
        'meta.published',
        'meta.created',
        'meta.updated'
      ]
    })
  }
}

export {
  Directory
}
