import React, {useContext} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {Form} from '#/main/app/content/form'
import {FormContent} from '#/main/app/content/form/containers/content'

import {EditorContext} from '#/main/app/editor/context'

const EditorPage = (props) => {
  const editorDef = useContext(EditorContext)

  return (
    <>
      <Form
        className="app-editor-form"
        name={editorDef.name}
        target={editorDef.target}
        onSave={editorDef.onSave}
        buttons={true}
      >
        <header className="d-flex flex-row align-items-center gap-2 mb-2" role="presentation">
          <h1 className="h4 m-0">
            {props.title}
          </h1>
          {props.managerOnly &&
            <span className="badge text-primary-emphasis bg-primary-subtle">{trans('confidentiality_manager')}</span>
          }
        </header>

        {props.help &&
          <p className="text-body-secondary">{props.help}</p>
        }

        {!isEmpty(props.definition) &&
          <FormContent
            level={5}
            disabled={props.disabled}
            name={editorDef.name}
            autoFocus={props.autoFocus}
            dataPart={props.dataPart}
            definition={props.definition}
          />
        }

        {props.children}
      </Form>

      <Toolbar
        className="app-editor-toolbar sticky-top"
        buttonName="btn btn-text-body"
        separatorName="my-2 border-top border-1"
        tooltip="left"
        toolbar={"close summary | " + props.actions.map(a => !['close', 'summary'].includes(a.name))}
        actions={[
          {
            name: 'close',
            label: trans('close'),
            icon: 'fa fa-fw fa-times',
            type: LINK_BUTTON,
            target: editorDef.close,
            exact: true
          }
        ].concat(props.actions || [])}
      />
    </>
  )
}

EditorPage.propTypes = {
  title: T.node.isRequired,
  help: T.string,
  children: T.any,
  managerOnly: T.bool,
  disabled: T.bool,
  autoFocus: T.bool,
  actions: T.arrayOf(T.shape({

  })),
  dataPart: T.string,
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  ))
}

EditorPage.defaultProps = {
  autoFocus: true,
  actions: []
}

export {
  EditorPage
}