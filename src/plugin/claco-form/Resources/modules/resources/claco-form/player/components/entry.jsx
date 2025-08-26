import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details'
import {formatField} from '#/main/app/content/form/parameters/utils'

import {MODAL_USERS} from '#/main/community/modals/users'

import {
  Field as FieldType,
  Entry as EntryType
} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {generateFromTemplate} from '#/plugin/claco-form/resources/claco-form/template'

import {ResourcePage} from '#/main/core/resource'
import {
  PageContent,
  PageHeading,
  PageHeadingSkeleton,
  PageSection,
  PageToolbar,
  PageToolbarSkeleton
} from '#/main/app/page'
import {Html} from '#/main/app/components/html'
import {ContentPublication, ContentPublicationSkeleton} from '#/main/app/content/components/publication'
import {Tags, TagsSkeleton} from '#/main/app/components/tags'
import {hasPermission} from '#/main/app/security'
import {TextSkeleton} from '#/main/app/components/placeholder'
import {EmptyState} from '#/main/app/components/empty-state'

const EntrySkeleton = ({
  entryDefinition = [],
  help = false,
  metadata = false,
  categories = false
}) => {
  return (
    <ResourcePage>
      <PageContent className="placeholder-glow">
        <PageToolbarSkeleton toolbar="edit more" />
        <PageHeadingSkeleton />

        <PageSection className="mb-5">
          {metadata &&
            <ContentPublicationSkeleton className="mb-4" />
          }

          {help &&
            <div className="bg-body-tertiary p-4 rounded-3 mb-4" role="presentation">
              <TextSkeleton className="mb-0" rows={3} />
            </div>
          }

          <DetailsData
            className="mb-4"
            definition={entryDefinition}
            loaded={false}
          />

          {categories &&
            <TagsSkeleton />
          }
        </PageSection>
      </PageContent>
    </ResourcePage>
  )
}

EntrySkeleton.propTypes = {
  entryDefinition: T.array,
  help: T.bool,
  metadata: T.bool,
  categories: T.bool
}

const Entry = (props) => {
  const canViewMetadata = props.canEdit ||
    props.displayMetadata === 'all' ||
    (props.displayMetadata === 'manager' && props.canAdministrate)

  const entryDefinition = [
    {
      id: 'general',
      title: trans('general'),
      primary: true,
      fields: props.fields
        .filter(field => isEmpty(field.restrictions.confidentiality)
          || 'none' === field.restrictions.confidentiality
          || props.canAdministrate
          || ('owner' === field.restrictions.confidentiality && props.isOwner)
        )
        .map(f => {
          const params = formatField(f, props.fields, 'values', true)

          switch (f.type) {
            case 'file':
              if (props.entry && props.entry.values && props.entry.values[f.id]) {
                params['calculated'] = (data) => Object.assign(
                  {},
                  data.values[f.id],
                  {url: url(['claro_claco_form_field_value_file_download', {entry: data.id, field: f.id}])}
                )
              }
              break
          }

          return params
        })
    }
  ]

  if (isEmpty(props.entry)) {
    return (
      <EntrySkeleton
        entryDefinition={entryDefinition}
        metadata={canViewMetadata}
        help={!!props.helpMessage}
        categories={props.displayCategories}
      />
    )
  }

  if (!props.canViewEntry) {
    return (
      <ResourcePage>
        <PageContent className="d-flex flex-column">
          <EmptyState
            className="p-4"
            icon="fa fa-lock"
            title={trans('entry_access_forbidden', {}, 'clacoform')}
            description={trans('entry_access_forbidden_help', {}, 'clacoform')}
            secondaryAction={{
              type: LINK_BUTTON,
              icon: 'fa fa-arrow-left',
              label: trans('back_home', {}, 'actions'),
              target: props.path,
              exact: true
            }}
          />
        </PageContent>
      </ResourcePage>
    )
  }

  return (
    <ResourcePage>
      <PageContent>
        <PageToolbar
          toolbar="edit more"
          actions={[
            {
              name: 'edit',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              target: `${props.path}/entry/form/${props.entry.id}`,
              displayed: !props.entry.locked && props.canEdit,
              group: trans('management'),
              primary: true
            }, {
              name: 'export-pdf',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-file-pdf',
              label: trans('export-pdf', {}, 'actions'),
              callback: () => props.downloadEntryPdf(props.entry.id),
              displayed: props.canDownload && hasPermission('open', props.entry),
              group: trans('transfer')
            }, {
              name: 'publish',
              type: CALLBACK_BUTTON,
              icon: classes('fa fa-fw', {
                'fa-eye-slash': 1 === props.entry.status,
                'fa-eye': 1 !== props.entry.status
              }),
              label: trans(props.entry.status === 1 ? 'unpublish':'publish', {}, 'actions'),
              callback: () => props.switchEntryStatus(props.entry.id),
              displayed: !props.entry.locked && props.canAdministrate,
              group: trans('management')
            }, {
              name: 'change-owner',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-user-edit',
              label: trans('change_owner', {}, 'actions'),
              modal: [MODAL_USERS, {
                multiple: false,
                selectAction: (users) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('change_owner', {}, 'actions'),
                  callback: () => props.changeEntryOwner(props.entry.id, users[0].id)
                })
              }],
              displayed: props.canAdministrate,
              group: trans('management')
            }, {
              name: 'lock',
              type: CALLBACK_BUTTON,
              icon: classes('fa fa-fw', {
                'fa-lock': !props.entry.locked,
                'fa-unlock': props.entry.locked
              }),
              label: trans(props.entry.locked ? 'unlock':'lock', {}, 'actions'),
              callback: () => props.switchEntryLock(props.entry.id),
              displayed: props.canAdministrate,
              group: trans('management')
            }, {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              callback: () => props.deleteEntry(props.entry).then(() => props.history.push(`${props.path}/entries`)),
              confirm: {
                title: trans('delete_entry', {}, 'clacoform'),
                message: trans('delete_entry_confirm_message', {title: props.entry.title}, 'clacoform')
              },
              dangerous: true,
              displayed: !props.entry.locked && props.canAdministrate,
              group: trans('management')
            }
          ]}
        />

        <PageHeading
          title={props.entry.title}
        />

        <PageSection className="mb-5">
          {canViewMetadata &&
            <ContentPublication
              className="mb-4"
              user={props.entry.user}
              publishedAt={get(props.entry, 'publicationDate')}
            />
          }

          {props.helpMessage &&
            <Html className="bg-body-tertiary p-4 rounded-3 mb-4">
              {props.helpMessage}
            </Html>
          }

          {props.template && props.useTemplate ?
            <Html className="mb-4">
              {generateFromTemplate(props.template, props.fields, props.entry, props.isOwner, props.canAdministrate)}
            </Html> :
            <DetailsData
              className="mb-4"
              data={props.entry}
              definition={entryDefinition}
            />
          }

          {((props.displayCategories || props.canAdministrate) && !isEmpty(props.entry.categories)) &&
            <Tags tags={props.entry.categories.map(category => category.name)} />
          }
        </PageSection>
      </PageContent>
    </ResourcePage>
  )
}

Entry.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  canDownload: T.bool.isRequired,
  canViewEntry: T.bool,

  isOwner: T.bool,

  helpMessage: T.string,
  displayMetadata: T.string.isRequired,
  displayCategories: T.bool.isRequired,

  template: T.string,
  useTemplate: T.bool.isRequired,
  titleLabel: T.string,

  entry: T.shape(EntryType.propTypes),
  fields: T.arrayOf(T.shape(FieldType.propTypes)),
  deleteEntry: T.func.isRequired,
  switchEntryStatus: T.func.isRequired,
  switchEntryLock: T.func.isRequired,
  downloadEntryPdf: T.func.isRequired,
  changeEntryOwner: T.func.isRequired,
  history: T.object.isRequired
}

export {
  Entry
}
