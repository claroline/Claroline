import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {url} from '#/main/app/api'
import {trans, displayDate} from '#/main/app/intl'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {LinkButton} from '#/main/app/buttons/link'

import {route as resourceRoute} from '#/main/core/resource/routing'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {getToolDocumentType} from '#/plugin/drop-zone/resources/dropzone/utils'
import {constants as configConstants} from '#/plugin/drop-zone/plugin/configuration/constants'
import {DocumentType} from '#/plugin/drop-zone/resources/dropzone/prop-types'

const formatUrl = (url) => !url || url.startsWith('http') ? url : `http://${url}`

const DocumentRow = props =>
  <tr className={classes('drop-document', {'manager-document': props.document.isManager})}>
    <td className="document-type">
      {constants.DOCUMENT_TYPES[props.document.type]}
    </td>

    {props.showUser &&
      <td>{`${props.document.user.firstName} ${props.document.user.lastName}`}</td>
    }

    {props.showMeta &&
      <td className="document-date">
        {displayDate(props.document.dropDate, false, true)}
      </td>
    }

    <td className="document-data">
      {props.document.type === constants.DOCUMENT_TYPE_FILE &&
        <a href={url(['claro_dropzone_document_download', {document: props.document.id}])}>
          {props.document.data.name}
        </a>
      }

      {props.document.type === constants.DOCUMENT_TYPE_TEXT &&
        <ContentHtml>{props.document.data}</ContentHtml>
      }

      {props.document.type === constants.DOCUMENT_TYPE_URL &&
        <a href={formatUrl(props.document.data)}>{formatUrl(props.document.data)}</a>
      }

      {props.document.type === constants.DOCUMENT_TYPE_RESOURCE &&
        <LinkButton
          target={resourceRoute(props.document.data)}
        >
          {props.document.data.name}
        </LinkButton>
      }
    </td>
    {(props.canEdit && !props.document.isManager) || (props.isManager && props.document.isManager) ?
      <td>
        <span
          className="fa fa-fw fa-trash-o pointer-hand"
          onClick={() => {
            props.showModal(MODAL_CONFIRM, {
              icon: 'fa fa-fw fa-trash-o',
              title: trans('delete_document', {}, 'dropzone'),
              question: trans('delete_document_confirm_message', {}, 'dropzone'),
              dangerous: true,
              handleConfirm: () => props.deleteDocument(props.document.id)
            })
          }}
        />
      </td> :
      <td></td>
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
  isManager: T.bool.isRequired,
  showUser: T.bool.isRequired,
  showMeta: T.bool.isRequired,
  showTools: T.bool.isRequired,
  document: T.shape(DocumentType.propTypes),
  tools: T.array,
  deleteDocument: T.func,
  executeTool: T.func,
  showModal: T.func
}

const Documents = props => {
  if (0 === props.documents.length) {
    return (
      <ContentPlaceholder
        icon="fa fa-fw fa-upload"
        title={trans('no_document', {}, 'dropzone')}
        help={trans('no_document_help', {}, 'dropzone')}
        size="lg"
      />
    )
  }

  return (
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
  )
}

Documents.propTypes = {
  canEdit: T.bool.isRequired,
  isManager: T.bool.isRequired,
  showUser: T.bool.isRequired,
  showMeta: T.bool.isRequired,
  showTools: T.bool.isRequired,
  documents: T.arrayOf(T.shape(DocumentType.propTypes)),
  tools: T.array
}

Documents.defaultProps = {
  documents: [],
  canEdit: false,
  isManager: false,
  showUser: false,
  showMeta: true,
  showTools: false
}

export {
  Documents
}
