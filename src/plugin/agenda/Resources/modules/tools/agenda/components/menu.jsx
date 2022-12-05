import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MENU_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Calendar} from '#/main/core/layout/calendar/components/calendar'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {ContentSummary} from '#/main/app/content/components/summary'
import {ColorChart} from '#/main/theme/color/components/color-chart'

import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

import {getEvents} from '#/plugin/agenda/events'
import {route} from '#/plugin/agenda/tools/agenda/routing'

class AgendaMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      eventTypes: []
    }
  }

  componentDidMount() {
    getEvents().then((eventApps) => {
      this.setState({eventTypes: eventApps.map(eventApp => eventApp.name)})
    })
  }

  render() {
    return (
      <MenuSection
        {...omit(this.props, 'history', 'path', 'view', 'selected')}
        className="agenda-menu"
        title={trans('agenda', {}, 'tools')}
      >
        <Calendar
          light={true}
          selected={this.props.selected}
          onChange={(selected) => {
            this.props.history.push(
              route(this.props.path, this.props.view, selected)
            )

            this.props.autoClose()
          }}
          time={false}
          showCurrent={false}
        />

        <h5 className="agenda-menu-title h-title">
          {trans('events', {}, 'agenda')}
        </h5>

        <ContentSummary
          links={this.state.eventTypes.map(eventType => ({
            id: eventType,
            type: CALLBACK_BUTTON,
            icon: -1 === this.props.types.indexOf(eventType) ? 'fa fa-fw fa-square' : 'fa fa-fw fa-check-square',
            label: trans(eventType, {}, 'event'),
            callback: () => {
              let newTypes = [].concat(this.props.types)
              if (-1 === this.props.types.indexOf(eventType)) {
                newTypes.push(eventType)
              } else {
                newTypes.splice(newTypes.indexOf(eventType), 1)
              }

              this.props.changeTypes(newTypes)
            }
          }))}
        />

        <h5 className="agenda-menu-title h-title">
          {trans('agendas', {}, 'agenda')}
        </h5>

        <ContentSummary
          links={this.props.plannings.map(planning => ({
            id: planning.id,
            type: CALLBACK_BUTTON,
            label: (
              <Fragment key="action-label">
                <span key="action-icon" className={classes('action-icon fa fa-fw icon-with-text-right', {
                  'fa-square': !planning.displayed,
                  'fa-check-square': planning.displayed
                })} style={{color: planning.color}}/>

                {planning.name}

                {undefined !== planning.loaded && !planning.loaded &&
                  <span className="planning-loader fa fa-fw fa-spinner fa-spin" />
                }
              </Fragment>
            ),
            callback: () => this.props.togglePlanning(planning.id),
            additional: [{
              name: 'menu',
              type: MENU_BUTTON,
              icon: 'fa fa-fw fa-ellipsis-v',
              label: trans('show-more-actions', {}, 'actions'),
              menu: (
                <ul className="dropdown-menu dropdown-menu-right">
                  {1 < this.props.plannings.length &&
                    <li>
                      <Button
                        type={CALLBACK_BUTTON}
                        icon="fa fa-fw fa-eye"
                        label={trans('Afficher uniquement cet agenda', {}, 'actions')}
                        callback={() => this.props.forcePlanning(planning.id)}
                      />
                    </li>
                  }

                  {!planning.locked &&
                    <li>
                      <Button
                        type={CALLBACK_BUTTON}
                        icon="fa fa-fw fa-trash"
                        label={trans('delete', {}, 'actions')}
                        callback={() => this.props.removePlanning(planning.id)}
                        dangerous={true}
                      />
                    </li>
                  }

                  {(1 < this.props.plannings.length || !planning.locked) &&
                    <li role="separator" className="divider" />
                  }

                  <li>
                    <ColorChart
                      showCurrent={false}
                      selected={planning.color}
                      onChange={(color) => this.props.changePlanningColor(planning.id, color)}
                    />
                  </li>
                </ul>
              )
            }]
          })).concat([
            {
              name: 'add-planning',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('add_agenda', {}, 'actions'),
              displayed: this.props.multiplePlannings,
              modal: [MODAL_WORKSPACES, {
                url: ['apiv2_workspace_list_registered'],
                selectAction: (workspaces) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('add', {}, 'actions'),
                  callback: () => workspaces.map(workspace => this.props.addPlanning(workspace.id, workspace.name))
                })
              }]
            }
          ])}
        />
      </MenuSection>
    )
  }
}

AgendaMenu.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,

  path: T.string.isRequired,
  view: T.oneOf([
    'day',
    'week',
    'month',
    'year',
    'schedule',
    'list'
  ]).isRequired,
  types: T.arrayOf(T.string).isRequired,
  selected: T.string.isRequired,
  plannings: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired,
    displayed: T.bool.isRequired,
    loaded: T.bool.isRequired,
    locked: T.bool,
    color: T.string
  })).isRequired,
  multiplePlannings: T.bool.isRequired,
  changeTypes: T.func.isRequired,
  addPlanning: T.func.isRequired,
  togglePlanning: T.func.isRequired,
  forcePlanning: T.func.isRequired,
  removePlanning: T.func.isRequired,
  changePlanningColor: T.func.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  AgendaMenu
}
