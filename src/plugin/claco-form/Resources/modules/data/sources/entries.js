import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {displayDate} from '#/main/app/intl/date'
import {constants as intlConstants} from '#/main/app/intl/constants'

import {canViewEntryMetadata} from '#/plugin/claco-form/resources/claco-form/permissions'

function formatFieldValue(entry, field, value, clacoForm, currentUser) {
  let formattedValue = ''

  if (field.restrictions.metadata && !canViewEntryMetadata(entry, clacoForm, false, currentUser)) {
    formattedValue = '-'
  } else {
    formattedValue = value

    if (value !== undefined && value !== null && value !== '') {
      switch (field.type) {
        case 'date':
          formattedValue = value.date ? displayDate(value.date) : displayDate(value)
          break
        case 'country':
          formattedValue = intlConstants.REGIONS[value]
          break
        case 'cascade':
          formattedValue = value.join(', ')
          break
        case 'choice':
          if (Array.isArray(value)) {
            formattedValue = value.join(', ')
          }
          break
      }
    }
  }

  return formattedValue
}

function getCardValue(clacoForm, row, type, currentUser) {
  let value = row.title
  let key = ''

  switch (type) {
    case 'title':
      key = clacoForm.details.display_title
      break
    case 'subtitle':
      key = clacoForm.details.display_subtitle
      break
    case 'content':
      key = clacoForm.details.display_content
      break
  }

  if (key && key !== 'title') {
    let field = {}

    switch (key) {
      case 'date':
        value = row.creationDate
        break
      case 'user':
        value = row.user ? `${row.user.firstName} ${row.user.lastName}` : trans('anonymous')
        break
      case 'categories':
        value = row.categories ? row.categories.map(c => c.name).join(', ') : ''
        break
      case 'keywords':
        value = row.keywords ? row.keywords.map(k => k.name).join(', ') : ''
        break
      default:
        if (row.values && row.values[key]) {
          field = clacoForm.fields.find(f => f.id === key)
          value = formatFieldValue(row, field, row.values[key], clacoForm, currentUser)
        } else {
          value = ''
        }
    }
  }

  return value
}

/**
 * ClacoForm entries source.
 *
 * NB. This is not registered as a standard Claroline source
 * because it requires additional data to be computed
 */
export default (clacoForm, canViewMetadata = false, canEdit = false, canAdministrate = false, isCategoryManager = false, path = null, currentUser = null) => {
  const fields = clacoForm.fields || []
  const titleLabel = get(clacoForm, 'details.title_field_label') || trans('title')
  const hasCategories = get(clacoForm, 'details.display_categories') || false
  const hasKeywords = get(clacoForm, 'details.display_keywords') || false

  return {
    name: 'entries',
    icon: 'fa fa-fw fa-file',
    parameters: {
      primaryAction: (row) => ({
        type: LINK_BUTTON,
        label: trans('open'),
        target: `${path}/entries/${row.id}`
      }),
      definition: [
        {
          name: 'type',
          label: trans('type'),
          displayable: false,
          displayed: false,
          sortable: false,
          filterable: true,
          type: 'choice',
          options: {
            choices: Object.assign({
              all_entries: trans('all_entries', {}, 'clacoform'),
              my_entries: trans('my_entries', {}, 'clacoform')
            }, isCategoryManager ? {
              manager_entries: trans('manager_entries', {}, 'clacoform')
            } : {})
          }
        }, {
          name: 'title',
          type: 'string',
          label: titleLabel ? titleLabel : trans('title'),
          primary: true,
          displayed: true
        }, {
          name: 'status',
          label: trans('published'),
          type: 'boolean',
          calculated: (rowData) => rowData.status === 1
        }, {
          name: 'locked',
          label: trans('locked'),
          type: 'boolean',
          displayed: canAdministrate,
          displayable: canAdministrate,
          filterable: canAdministrate,
          sortable: canAdministrate
        },
        // Metadata
        {
          name: 'creationDate',
          label: trans('date'),
          type: 'date',
          filterable: false,
          displayed: canViewMetadata,
          displayable: canViewMetadata,
          sortable: canViewMetadata,
          calculated: (rowData) => canViewEntryMetadata(rowData, clacoForm, canEdit, currentUser) ? rowData.creationDate : null
        }, {
          name: 'createdAfter',
          label: trans('created_after'),
          type: 'date',
          filterable: canViewMetadata,
          displayable: false,
          sortable: false,
          options: {time: true}
        }, {
          name: 'createdBefore',
          label: trans('created_before'),
          type: 'date',
          filterable: canViewMetadata,
          displayable: false,
          sortable: false,
          options: {time: true}
        }, {
          name: 'user',
          label: trans('user'),
          sortable: false,
          filterable: canViewMetadata,
          displayed: canViewMetadata,
          displayable: canViewMetadata,
          calculated: (rowData) => canViewEntryMetadata(rowData, clacoForm, canEdit, currentUser) && rowData.user ? `${rowData.user.firstName} ${rowData.user.lastName}` : null
        },
        // Categories
        {
          name: 'categories',
          label: trans('categories'),
          type: 'string',
          displayed: hasCategories,
          displayable: hasCategories,
          filterable: false,
          sortable: false,
          calculated: (rowData) => rowData.categories ? rowData.categories.map(c => c.name).join(', ') : ''
        }, {
          name: 'category',
          label: trans('category'),
          type: 'choice',
          sortable: false,
          displayed: false,
          displayable: false,
          filterable: hasCategories,
          options: {
            choices: clacoForm.categories ? clacoForm.categories.reduce((acc, category) => Object.assign(acc, {
              [category.id]: category.name
            }), {}) : {}
          }
        },
        // Keywords
        {
          name: 'keywords',
          label: trans('keywords', {}, 'clacoform'),
          type: 'string',
          displayed: hasKeywords,
          displayable: hasKeywords,
          filterable: hasKeywords,
          sortable: false,
          calculated: (rowData) => rowData.keywords ? rowData.keywords.map(k => k.name).join(', ') : ''
        }
      ].concat(
        // Fields defined in ClacoForm
        fields
          .filter(f => !f.restrictions.hidden && (!f.restrictions.metadata || canViewMetadata))
          .map(field => {
            const options = field.options ? Object.assign({}, field.options) : {}

            // TODO : must use same format to avoid ugly remap
            // change choices format to make it acceptable by ui components
            if ('choice' === field.type) {
              options.choices = options.choices.reduce((acc, choice) => Object.assign(acc, {
                [choice.value]: choice.label
              }), {})
            }

            return {
              name: 'values.' + field.id,
              label: field.label,
              type: field.type,
              options: options
            }
          })
      ),
      card: (props) => {
        const EntryCard = React.createElement(DataCard, Object.assign({}, props, {
          id: props.data.id,
          icon: React.createElement(UserAvatar, {
            picture: props.data.user ? props.data.user.picture : undefined,
            alt: true
          }),
          title: getCardValue(clacoForm, props.data, 'title', currentUser),
          subtitle: getCardValue(clacoForm, props.data, 'subtitle', currentUser),
          contentText: getCardValue(clacoForm, props.data, 'content', currentUser)
        }))

        return EntryCard
      }
    }
  }
}
