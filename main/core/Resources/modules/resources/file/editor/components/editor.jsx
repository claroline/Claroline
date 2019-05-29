import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {theme} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {Await} from '#/main/app/components/await'

import {getFile, getTypeName} from '#/main/core/files'
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
        level={2}
        title={trans('parameters')}
        name={selectors.FORM_NAME}
        buttons={true}
        target={['apiv2_resource_file_update', {id: this.props.file.id}]}
        cancel={{
          type: LINK_BUTTON,
          target: '/',
          exact: true
        }}
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
              }, {
                name: 'commentsActivated',
                label: trans('activate_comments'),
                type: 'boolean',
                displayed: -1 < this.props.mimeType.indexOf('video')
              }
            ]
          }
        ]}
      >
        <Await
          for={getFile(this.props.mimeType)}
          then={module => {
            if (get(module, 'fileType.components.editor')) {
              return (
                <FormSections level={3}>
                  <FormSection
                    className="embedded-list-section"
                    title={trans(getTypeName(this.props.mimeType))}
                  >
                    {React.createElement(get(module, 'fileType.components.editor'), {
                      file: this.props.file
                    })}

                    {get(module, 'fileType.styles') &&
                      <link rel="stylesheet" type="text/css" href={theme(get(module, 'fileType.styles'))} />
                    }
                  </FormSection>
                </FormSections>
              )
            }
            return null
          }}
        />
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
