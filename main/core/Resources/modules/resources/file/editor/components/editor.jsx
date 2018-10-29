import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/core/resources/file/editor/store/selectors'

// TODO : find a way to make this kind of component generic (duplicated for all apps coming from dynamic loading)
// TODO : find a way to reuse file creation form component

class Editor extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fileEditor: null,
      fileStyles: null
    }
  }

  render() {
    //bad.
    return (
      <FormData
        level={5}
        name={selectors.FORM_NAME}
        buttons={true}
        target={['apiv2_resource_file_update', {id: this.props.file.id}]}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'autoDownload',
                label: trans('auto_download'),
                type: 'boolean',
                required: true
              }
            ]
          }
        ]}
      >
      </FormData>
    )
  }
}

Editor.propTypes = {
  mimeType: T.string.isRequired,
  file: T.shape({
    id: T.number.isRequired
  }).isRequired
}

const FileEditor = connect(
  (state) => ({
    mimeType: selectors.mimeType(state),
    file: selectors.file(state)
  })
)(Editor)

export {
  FileEditor
}
