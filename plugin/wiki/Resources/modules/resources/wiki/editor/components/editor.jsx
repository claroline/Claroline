import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'

import {Wiki as WikiTypes} from '#/plugin/wiki/resources/wiki/prop-types'
import {WIKI_MODES, WIKI_MODE_CHOICES} from '#/plugin/wiki/resources/wiki/constants'

const EditorComponent = props =>
  <FormData
    level={2}
    buttons={true}
    target={() => ['apiv2_wiki_update_options', {id: props.wiki.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    title={trans('configure', {}, 'platform')}
    name="wikiForm"
    sections={[
      {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters', {}, 'platform'),
        fields: [
          {
            name: 'display.sectionNumbers',
            help: trans('display_section_numbers_message', {}, 'icap_wiki'),
            type: 'boolean',
            label: trans('display_section_numbers', {}, 'icap_wiki')
          }, {
            name: 'display.contents',
            type: 'boolean',
            label: trans('display_contents', {}, 'icap_wiki')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-gavel',
        title: trans('moderation', {}, 'icap_wiki'),
        fields: [
          {
            name: 'mode',
            type: 'choice',
            label: trans('icap_wiki_options_type_mode', {}, 'icap_wiki'),
            help: trans(WIKI_MODES[props.wiki.mode]+'_message', {}, 'icap_wiki'),
            required: true,
            options: {
              noEmpty: true,
              condensed: false,
              choices: WIKI_MODE_CHOICES
            }
          }
        ]
      }
    ]}
  />

EditorComponent.propTypes = {
  wiki: T.shape(WikiTypes.propTypes)
}

const Editor = connect(
  state => ({
    wiki: formSelect.data(formSelect.form(state, 'wikiForm'))
  })
)(EditorComponent)

export {
  Editor
}
