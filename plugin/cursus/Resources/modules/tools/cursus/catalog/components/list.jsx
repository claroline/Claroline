import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {ToolPage} from '#/main/core/tool/containers/page'

import {route} from '#/plugin/cursus/routing'
import {CourseCard} from '#/plugin/cursus/course/components/card'
import {selectors} from '#/plugin/cursus/tools/cursus/catalog/store'

class CatalogList extends Component {
  constructor(props) {
    super(props)

    this.state = {
      section: 'available'
    }
  }

  render() {
    return (
      <ToolPage
        path={[{
          type: LINK_BUTTON,
          label: trans('catalog', {}, 'cursus'),
          target: `${this.props.path}/catalog`
        }]}
        subtitle={trans('catalog', {}, 'cursus')}
        primaryAction="add"
        actions={[
          {
            name: 'add',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_course', {}, 'cursus'),
            target: `${this.props.path}/catalog/new`,
            group: trans('management'),
            primary: true
          }
        ]}
      >
        <header className="row content-heading">
          <ContentTabs
            sections={[
              {
                name: 'all',
                type: CALLBACK_BUTTON,
                label: trans('Toutes les formations', {}, 'cursus'),
                callback: () => this.setState({section: 'all'}),
                active: 'all' === this.state.section
              }, {
                name: 'available',
                type: CALLBACK_BUTTON,
                label: trans('Formations disponibles', {}, 'cursus'),
                target: `${this.props.path}/catalog/available`,
                callback: () => this.setState({section: 'available'}),
                active: 'available' === this.state.section
              }
            ]}
          />
        </header>

        <ListData
          name={selectors.LIST_NAME}
          fetch={{
            url: ['all' === this.state.section ? 'apiv2_cursus_course_list' : 'apiv2_cursus_course_available'],
            autoload: true
          }}
          delete={{
            url: ['apiv2_cursus_course_delete_bulk'],
            displayed: (rows) => -1 !== rows.findIndex(course => hasPermission('delete', course))
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            label: trans('open', {}, 'actions'),
            target: route(row)
          })}
          definition={[
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              displayed: true,
              primary: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code'),
              displayed: true
            }, {
              name: 'tags',
              type: 'tag',
              label: trans('tags'),
              displayed: true,
              sortable: false,
              options: {
                objectClass: 'Claroline\\CursusBundle\\Entity\\Course'
              }
            }, {
              name: 'meta.order',
              alias: 'order',
              type: 'number',
              label: trans('order'),
              displayable: false,
              filterable: false
            }
          ]}
          actions={(rows) => [
            {
              name: 'edit',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              target: route(rows[0]) + '/edit',
              displayed: hasPermission('delete', rows[0]),
              group: trans('management'),
              scope: ['object']
            }
          ]}
          card={CourseCard}
          display={{
            current: listConst.DISPLAY_LIST
          }}
        />
      </ToolPage>
    )
  }
}


CatalogList.propTypes = {
  path: T.string.isRequired
}

export {
  CatalogList
}
