import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/theme/config'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {Await} from '#/main/app/components/await'

import {getFile, getTypeName} from '#/main/core/files'
import {constants} from '#/main/core/resources/file/constants'
import {selectors} from '#/main/core/resources/file/editor/store/selectors'

// TODO : find a way to reuse file creation form component

const EditorMain = (props) =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    buttons={true}
    target={['apiv2_resource_file_update', {id: props.file.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'opening',
            label: trans('opening_parameters'),
            type: 'choice',
            required: true,
            options: {
              noEmpty: true,
              choices: constants.OPENING_TYPES
            }
          }, {
            name: 'commentsActivated',
            label: trans('activate_comments'),
            type: 'boolean',
            displayed: -1 < props.mimeType.indexOf('video')
          }
        ]
      }
    ]}
  >
    <Await
      for={getFile(props.mimeType)}
      then={module => {
        if (get(module, 'fileType.components.editor')) {
          return (
            <FormSections level={3}>
              <FormSection
                className="embedded-list-section"
                title={trans(getTypeName(props.mimeType) + '_section')}
              >
                {createElement(get(module, 'fileType.components.editor'), {
                  file: props.file,
                  path: props.path
                })}

                {get(module, 'fileType.styles') &&
                  <Helmet>
                    {get(module, 'fileType.styles').map(style =>
                      <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
                    )}
                  </Helmet>
                }
              </FormSection>
            </FormSections>
          )
        }

        return null
      }}
    />
  </FormData>

EditorMain.propTypes = {
  path: T.string.isRequired,
  mimeType: T.string.isRequired,
  file: T.shape({
    id: T.number.isRequired
  }).isRequired
}

export {
  EditorMain
}
