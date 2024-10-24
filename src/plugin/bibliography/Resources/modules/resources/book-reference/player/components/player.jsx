import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {trans} from '#/main/app/intl/translation'
import {Alert} from '#/main/app/components/alert'
import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors} from '#/plugin/bibliography/resources/book-reference/store'

const PlayerComponent = props =>
  <Row>
    {props.bookReference.cover &&
      <Col md={3}>
        <img
          className="img-fluid"
          src={props.bookReference.cover}
          alt={trans('cover', {}, 'icap_bibliography')}
          title={trans('cover', {}, 'icap_bibliography')}
        />
      </Col>
    }
    <Col md={props.bookReference.cover ? 9 : 12}>
      {props.canEdit &&
        <Alert type="warning" title={trans('deprecated_resource', {}, 'platform')} className="component-container content-md mt-3">
          {trans('deprecated_resource_message', {}, 'platform')}
        </Alert>
      }

      <DetailsData
        level={3}
        name={selectors.STORE_NAME+'.bookReference'}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'author',
                type: 'string',
                label: trans('author', {}, 'icap_bibliography')
              }, {
                name: 'isbn',
                type: 'string',
                label: trans('isbn', {}, 'icap_bibliography')
              }, {
                name: 'abstract',
                type: 'string',
                label: trans('abstract', {}, 'icap_bibliography')
              }
            ]
          },
          {
            icon: 'fa fa-fw fa-circle-info',
            title: trans('information'),
            fields: [
              {
                name: 'publisher',
                type: 'string',
                label: trans('publisher', {}, 'icap_bibliography')
              }, {
                name: 'printer',
                type: 'string',
                label: trans('printer', {}, 'icap_bibliography')
              }, {
                name: 'publicationYear',
                type: 'number',
                label: trans('publication_year', {}, 'icap_bibliography')
              }, {
                name: 'language',
                type: 'string',
                label: trans('language', {}, 'icap_bibliography')
              }, {
                name: 'pages',
                type: 'number',
                label: trans('page_count', {}, 'icap_bibliography')
              }, {
                name: 'url',
                type: 'url',
                label: trans('url', {}, 'icap_bibliography')
              }
            ]
          }
        ]}
      />
    </Col>
  </Row>

PlayerComponent.propTypes = {
  bookReference: T.object.isRequired,
  canEdit: T.bool.isRequired
}

const Player = connect(
  state => ({
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    bookReference: formSelect.originalData(formSelect.form(state, selectors.STORE_NAME+'.bookReference'))
  })
)(PlayerComponent)

export {
  Player
}
