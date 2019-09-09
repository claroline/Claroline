import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Toggle} from '#/main/app/input/components/toggle'
import {Button} from '#/main/app/action/components/button'
//import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

const apps = [
  {
    icon: 'fab fa-fw fa-dropbox',
    alt: 'fab fa-fw fa-dropbox',
    name: 'dropbox',
    active: false
  }, {
    icon: 'fab fa-fw fa-facebook-square',
    alt: 'fab fa-fw fa-facebook-f',
    name: 'facebook',
    active: false
  }, {
    icon: 'fab fa-fw fa-github',
    alt: 'fab fa-fw fa-github-alt',
    name: 'github',
    active: true
  }, {
    icon: 'fab fa-fw fa-google',
    alt: 'fab fa-fw fa-google',
    name: 'google',
    active: true
  }, {
    icon: 'fab fa-fw fa-linkedin',
    alt: 'fab fa-fw fa-linkedin-in',
    name: 'linkedin',
    active: false
  }, {
    icon: 'fab fa-fw fa-windows',
    alt: 'fab fa-fw fa-windows',
    name: 'office_365',
    active: true
  }, {
    icon: 'fab fa-fw fa-windows',
    alt: 'fab fa-fw fa-windows',
    name: 'windows_live',
    active: false
  }
]

const ExternalApp = props =>
  <article className={classes('external-app', props.name, {
    enabled: props.active,
    disabled: !props.active
  })}>
    <span className={classes('external-app-icon', props.alt)} />

    <h2 className="h3 external-app-title">
      {trans(props.name, {}, 'oauth')}

      <small>
        <b>ID:</b> 1_i84c8dzlc9skw48w444gg4csokcsoo4g8wk48c80csoss440o
      </small>
      <small>
        <b>Secret:</b> w2umhdg83ms4o8ggs4c8ggc04wk08wkc8ssg888sg08kkggo0
      </small>
    </h2>

    <Toggle
      active={props.active}
      onChange={() => true}
    />

  </article>

ExternalApp.propTypes = {
  icon: T.string.isRequired,
  alt: T.string.isRequired,
  name: T.string.isRequired,
  active: T.bool.isRequired
}

class ExternalAdministration extends Component {
  constructor(props) {
    super(props)

    this.state = {
      filter: 'all'
    }
  }

  render() {
    return (
      <ToolPage
        path={[{
          type: LINK_BUTTON,
          label: trans('external', {}, 'integration'),
          target: `${this.props.path}/external`
        }]}
        subtitle={trans('external', {}, 'integration')}
      >
        <div className="external-apps-filter">
          Afficher:
          <Button
            id="list-filter-app"
            className="btn btn-link"
            type={MENU_BUTTON}
            label={classes({
              'Toutes les applications': 'all' === this.state.filter,
              'Uniquement les applications actives': 'active' === this.state.filter,
              'Uniquement les applications inactives': 'inactive' === this.state.filter
            })}
            primary={true}
            menu={{
              align: 'right',
              items: [
                {
                  type: CALLBACK_BUTTON,
                  label: 'Toutes les applications',
                  active: 'all' === this.state.filter,
                  callback: () => this.setState({filter: 'all'})
                }, {
                  type: CALLBACK_BUTTON,
                  label: 'Uniquement les applications actives',
                  active: 'active' === this.state.filter,
                  callback: () => this.setState({filter: 'active'})
                }, {
                  type: CALLBACK_BUTTON,
                  label: 'Uniquement les applications inactives',
                  active: 'inactive' === this.state.filter,
                  callback: () => this.setState({filter: 'inactive'})
                }
              ]
            }}
          />
        </div>

        {apps
          .filter(app => {
            if ('active' === this.state.filter) {
              return app.active
            }

            if ('inactive' === this.state.filter) {
              return !app.active
            }

            return true
          })
          .map(app =>
            <ExternalApp
              key={app.name}
              {...app}
            />
          )
        }
      </ToolPage>
    )
  }
}


ExternalAdministration.propTypes = {
  path: T.string.isRequired
}

export {
  ExternalAdministration
}
