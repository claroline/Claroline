import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {generateUrl} from '#/main/core/api/router'
import {trans} from '#/main/core/translation'
import {asset} from '#/main/core/scaffolding/asset'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {getToolDocumentType} from '#/plugin/drop-zone/resources/dropzone/utils'
import {constants as configConstants} from '#/plugin/drop-zone/plugin/configuration/constants'
import {DocumentType} from '#/plugin/drop-zone/resources/dropzone/prop-types'

const DocumentActions = () =>
  <div className="document-actions">
  </div>

DocumentActions.propTypes = {

}

const Document = props =>
  <li className="document">
    <span className={classes('fa', {
      'fa-file-o': constants.DOCUMENT_TYPE_FILE === props.type,
      'fa-pencil': constants.DOCUMENT_TYPE_TEXT === props.type,
      'fa-link': constants.DOCUMENT_TYPE_URL === props.type,
      'fa-folder-open': constants.DOCUMENT_TYPE_RESOURCE === props.type
    })} />

    {constants.DOCUMENT_TYPES[props.type]}

    <DocumentActions

    />
  </li>

Document.propTypes = {
  user: T.shape({}),
  type: T.string.isRequired
}

const DocumentRow = props =>
  <tr className="drop-document">
    <td className="document-type">
      {constants.DOCUMENT_TYPES[props.document.type]}
    </td>

    {props.showUser &&
      <td>{`${props.document.user.firstName} ${props.document.user.lastName}`}</td>
    }

    {props.showMeta &&
      <td className="document-date">
        {props.document.dropDate}
      </td>
    }

    <td className="document-data">
      {props.document.type === constants.DOCUMENT_TYPE_FILE &&
        <a href={asset(props.document.data.url)} download={props.document.data.name}>
          {props.document.data.name}
        </a>
      }

      {props.document.type === constants.DOCUMENT_TYPE_TEXT &&
        <HtmlText>{props.document.data}</HtmlText>
      }

      {props.document.type === constants.DOCUMENT_TYPE_URL &&
        <a href={props.document.data}>{props.document.data}</a>
      }

      {props.document.type === constants.DOCUMENT_TYPE_RESOURCE &&
        <a href={generateUrl('claro_resource_open_short', {node: props.document.data.actualId})}>
          {props.document.data.name}
        </a>
      }
    </td>
    {props.canEdit &&
      <td>
        <span
          className="fa fa-fw fa-trash pointer-hand"
          onClick={() => {
            props.showModal(MODAL_DELETE_CONFIRM, {
              title: trans('delete_document', {}, 'dropzone'),
              question: trans('delete_document_confirm_message', {}, 'dropzone'),
              handleConfirm: () => props.deleteDocument(props.document.id)
            })
          }}
        />
      </td>
    }
    {props.showTools && props.tools.length > 0 &&
      <td>
        {props.tools.map(t =>
          <button
            key={`tool-btn-${t.id}`}
            className="btn btn-default"
            type="button"
            onClick={() => props.executeTool(t.id, props.document.id)}
          >
            {t.name}
          </button>
        )}
        {props.document.toolDocuments.length > 0 && props.document.toolDocuments.map(td => {
          if (getToolDocumentType(td, props.tools) === configConstants.compilatioValue && td.data && td.data.reportUrl) {
            return (
              <button
                key={`tool-document-button-${td.id}`}
                className="btn btn-default"
                type="button"
                onClick={() => window.open(td.data.reportUrl, '_blank')}
              >
                {trans('report', {}, 'dropzone')}
              </button>
            )
          } else {
            return ''
          }
        })}
      </td>
    }
  </tr>

DocumentRow.propTypes = {
  canEdit: T.bool.isRequired,
  showUser: T.bool.isRequired,
  showMeta: T.bool.isRequired,
  showTools: T.bool.isRequired,
  document: T.shape(DocumentType.propTypes),
  tools: T.array,
  deleteDocument: T.func,
  executeTool: T.func,
  showModal: T.func
}

const Documents = props =>
  <section>
    <h3>{trans('documents_added_to_copy', {}, 'dropzone')}</h3>

    {0 !== props.documents.length &&
      <table className="table">
        <thead>
          <tr>
            <th>{trans('drop_type', {}, 'dropzone')}</th>
            {props.showUser &&
            <th>{trans('user', {}, 'platform')}</th>
            }
            {props.showMeta &&
            <th>{trans('drop_date', {}, 'dropzone')}</th>
            }
            <th>{trans('document', {}, 'dropzone')}</th>
            {props.canEdit &&
            <th>{trans('actions', {}, 'platform')}</th>
            }
            {props.showTools && props.tools.length > 0 &&
            <th>{trans('tools', {}, 'platform')}</th>
            }
          </tr>
        </thead>

        <tbody>
          {props.documents.map(d =>
            <DocumentRow
              key={`document-${d.id}`}
              document={d}
              {...props}
            />
          )}
        </tbody>
      </table>
    }

    {0 === props.documents.length &&
      <div className="alert alert-warning">
        {trans('no_document', {}, 'dropzone')}
      </div>
    }
  </section>

Documents.propTypes = {
  canEdit: T.bool.isRequired,
  showUser: T.bool.isRequired,
  showMeta: T.bool.isRequired,
  showTools: T.bool.isRequired,
  documents: T.arrayOf(T.shape(DocumentType.propTypes)),
  tools: T.array
}

Documents.defaultProps = {
  canEdit: false,
  showUser: false,
  showMeta: true,
  showTools: false
}

export {
  Documents
}
