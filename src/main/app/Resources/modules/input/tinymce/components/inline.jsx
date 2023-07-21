import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Editor} from '@tinymce/tinymce-react'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

/**
 * @internal
 */
const TinymceInline = (props) =>
  <div className="tinymce-inline" style={{
    minHeight: `${props.minRows * 34}px`,
    maxHeight: 500
  }}>
    <Editor
      {...omit(props, 'minRows', 'init')}
      inline={true}
      init={merge({}, props.init, {
        toolbar: false,
        menubar: false,
        // we don't need to reload our default styles because the editor is directly mounted inside claroline page,
        // and already inherits our styles
        content_css: null
      })}
    />
  </div>

TinymceInline.propTypes = {
  init: T.object,
  minRows: T.number
}

export {
  TinymceInline
}
