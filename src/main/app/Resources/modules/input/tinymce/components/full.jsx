import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Editor} from '@tinymce/tinymce-react'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

/**
 * @internal
 */
const TinymceFull = (props) =>
  <Editor
    {...omit(props, 'minRows', 'init')}
    inline={false}
    init={merge({}, props.init, {
      height: '100%',
      menubar: 'edit view insert format help',
      menu: {
        view: {
          title: 'View',
          items: 'wordcount | visualaid visualchars visualblocks | preview code'
        },
        insert: {
          title: 'Insert',
          items: 'resource-picker file placeholders | image link media template inserttable | formula charmap emoticons hr codesample | insertdatetime'
        }
      },
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough ' +
        '| forecolor backcolor removeformat | alignleft aligncenter alignright alignjustify | outdent indent ' +
        '| numlist bullist | link resource-picker file placeholders insertfile image media table'
    })}
  />

TinymceFull.propTypes = {
  id: T.string.isRequired,
  init: T.object
}

export {
  TinymceFull
}
