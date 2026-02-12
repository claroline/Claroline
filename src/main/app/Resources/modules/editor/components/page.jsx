import React, {useContext, useId} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {Form} from '#/main/app/content/form'
import {FormContent} from '#/main/app/content/form/containers/content'

import {EditorContext} from '#/main/app/editor/context'
import {Badge} from '#/main/app/components/badge'
import {Heading} from '#/main/app/components/heading'
import {Helmet} from 'react-helmet'

const EditorPage = (props) => {
  const editorDef = useContext(EditorContext)

  const titleId = useId()
  const descriptionId = useId()

  return (
    <>
      <Helmet>
        <title>{editorDef.title} - {props.title}</title>
      </Helmet>

      <Form
        className="app-editor-form flex-fill"
        name={editorDef.name}
        level={2}
        target={editorDef.target}
        onSave={editorDef.onSave}
        buttons={true}
        aria-labelledby={titleId}
        aria-describedby={props.help ? descriptionId : undefined}
      >
        <header className="">
          <div className="d-flex flex-row align-items-center gap-2" role="presentation">
            <Heading id={titleId} level={2} displayLevel={4} className="app-editor-title m-0">
              {props.title}
            </Heading>

            {props.managerOnly &&
              <Badge variant="primary" subtle={true}>{trans('confidentiality_manager')}</Badge>
            }
          </div>

          {props.help &&
            <p id={descriptionId} className="lead text-body-secondary mt-2 mb-0">{props.help}</p>
          }

          {props.meta}
        </header>

        {!isEmpty(props.definition) &&
          <FormContent
            level={3}
            displayLevel={5}
            disabled={props.disabled}
            name={editorDef.name}
            dataPart={props.dataPart}
            definition={props.definition}
            locked={props.locked}
          />
        }

        {props.children}
      </Form>

      <Toolbar
        className="app-editor-toolbar sticky-top"
        buttonName="btn btn-text-body focus-ring focus-ring-secondary"
        separatorName="my-2 border-top border-1"
        tooltip="left"
        toolbar={'close summary | ' + (props.actions ? props.actions.map(a => !['close', 'summary'].includes(a.name)) : '')}
        actions={[
          {
            name: 'close',
            label: trans('close', {}, 'actions'),
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
  meta: T.any,
  children: T.any,
  managerOnly: T.bool,
  disabled: T.bool,
  actions: T.arrayOf(T.shape({

  })),
  dataPart: T.string,
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  locked: T.arrayOf(T.string)
}

export {
  EditorPage
}
