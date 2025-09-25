import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {displayDate, trans} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action'
import {EmptyState} from '#/main/app/components/empty-state'
import {selectors as securitySelectors} from '#/main/app/security'

import {Certificate} from '#/main/evaluation/prop-types'
import {getCertificateActions} from '#/main/evaluation/workspace/utils'

const UserProgressionCertificates = ({certificates}) => {
  const currentUser = useSelector(securitySelectors.currentUser)

  if (isEmpty(certificates)) {
    return (
      <EmptyState
        className="py-5"
        title={trans('Aucun certificate obtenu pour le moment')}
      />
    )
  }

  return (
    <>
      <p className="text-body-secondary fs-sm">Retrouvez tous les certificats obtenus pour cet espace.</p>

      <ul className="list-group">
        {certificates.map(certificate =>
          <li key={certificate.id} className="list-group-item d-flex flex-row gap-2 align-items-baseline">
            <span className="fa fa-file-pdf" />
            Certificat du {displayDate(certificate.obtentionDate)}
            <span className="fs-sm text-body-secondary">émis le {displayDate(certificate.issueDate, false, true)}</span>

            <Toolbar
              className="ms-auto me-n2"
              buttonName="btn btn-link"
              actions={getCertificateActions([certificate], {}, null, currentUser, true)
                .then(actions => actions.map(action => Object.assign({}, action, {
                  icon: undefined
                })))
              }
              size="sm"
            />
          </li>
        )}
      </ul>
    </>
  )
}

UserProgressionCertificates.propTypes = {
  certificates: T.arrayOf(T.shape(
    Certificate.propTypes
  ))
}

export {
  UserProgressionCertificates
}
