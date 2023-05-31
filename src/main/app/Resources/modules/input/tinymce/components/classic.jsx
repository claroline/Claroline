import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Editor} from '@tinymce/tinymce-react'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

/**
 * @internal
 */
const TinymceClassic = (props) =>
  <Editor
    {...omit(props, 'minRows', 'init')}
    init={merge({}, props.init, {
      // customize toolbars
      menubar: false,
      toolbar: 'insert blocks fontsize | bold italic underline forecolor | alignleft aligncenter alignright alignjustify' + // undo redo
        '| bullist numlist | removeformat', // | outdent indent | fullscreen preview code help

      toolbar_groups: {
        insert: {
          icon: 'plus',
          tooltip: 'Insert',
          items: 'resource-picker file placeholders | link image media table | formula charmap emoticons hr | insertdatetime'
        }
      },

      // plugin autoresize
      plugins: ['autoresize'],
      min_height: `${props.minRows * 34}px`,
      max_height: 500
    })}
  />

TinymceClassic.propTypes = {
  init: T.object,
  minRows: T.number
}

export {
  TinymceClassic
}
