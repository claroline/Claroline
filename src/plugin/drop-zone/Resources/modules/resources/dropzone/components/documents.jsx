import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {url} from '#/main/app/api'
import {trans, displayDate} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link'

import {route as resourceRoute} from '#/main/core/resource/routing'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
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
        <Button
          className="btn btn-link"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-trash-o"
          label={trans('delete', {}, 'actions')}
          callback={() => props.deleteDocument(props.document.id)}
          tooltip="left"
          dangerous={true}
          confirm={{
            title: trans('delete_document', {}, 'dropzone'),
            message: trans('delete_document_confirm_message', {}, 'dropzone')
          }}
        />
      </td> :
      <td></td>
    }
  </tr>

DocumentRow.propTypes = {
  canEdit: T.bool.isRequired,
  isManager: T.bool.isRequired,
  showUser: T.bool.isRequired,
  showMeta: T.bool.isRequired,
  document: T.shape(DocumentType.propTypes),
  deleteDocument: T.func,
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
  documents: T.arrayOf(T.shape(DocumentType.propTypes))
}

Documents.defaultProps = {
  documents: [],
  canEdit: false,
  isManager: false,
  showUser: false,
  showMeta: true
}

export {
  Documents
}
