import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {PageSimple} from '#/main/app/page/components/simple'

import {MODAL_LOGIN} from '#/main/app/modals/login'
import {MODAL_REGISTRATION} from '#/main/app/modals/registration'
import {getSso} from '#/main/authentication/sso'

class HomeExternalAccountComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      sso: null
    }
  }

  componentDidMount() {
    this.changeSso(this.props.serviceName)
  }

  componentDidUpdate(prevProps) {
    if (prevProps.serviceName !== this.props.serviceName) {
      this.changeSso(this.props.serviceName)
    }
  }

  changeSso(serviceName) {
    getSso(serviceName).then(sso => this.setState({sso: sso.default}))
  }

  render() {
    return (
      <PageSimple
        className="authentication-page login-page"
      >
        {this.state.sso &&
          <div className={classes('external-link-container', this.props.serviceName)}>
            <div className="img-thumbnail">
              <span className={classes(this.state.sso.alt, 'external-app-icon')} />
            </div>

            <div className="external-link-panel">
              <h1 className="external-link-title h2">
                <small>Lier mon compte</small>
                {this.state.sso.label}
              </h1>

              {!this.props.isAuthenticated &&
                <Button
                  style={{marginTop: 20}}
                  className="btn btn-block btn-emphasis"
                  type={MODAL_BUTTON}
                  label={trans('login', {}, 'actions')}
                  modal={[MODAL_LOGIN, {
                    onLogin: (response) => this.props.linkExternalAccount(this.props.serviceName, response.user.username).then(() => {
                      // TODO : redirect
                      // TODO : connection message
                      this.props.history.push('/desktop')
                    })
                  }]}
                  primary={true}
                />
              }

              {!this.props.isAuthenticated && this.props.selfRegistration &&
                <Button
                  className="btn btn-block"
                  type={MODAL_BUTTON}
                  label={trans('create-account', {}, 'actions')}
                  modal={[MODAL_REGISTRATION, {
                    onRegister: (user) => this.props.linkExternalAccount(this.props.serviceName, user.username).then(() => {
                      // TODO : redirect
                      // TODO : connection message
                      this.props.history.push('/desktop')
                    })
                  }]}
                />
              }
            </div>
          </div>
        }
      </PageSimple>
    )
  }
}


HomeExternalAccountComponent.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }),
  selfRegistration: T.bool.isRequired,
  isAuthenticated: T.bool.isRequired,

  serviceName: T.string.isRequired,
  serviceUserId: T.string.isRequired,
  linkExternalAccount: T.func.isRequired
}

const HomeExternalAccount = withRouter(HomeExternalAccountComponent)

export {
  HomeExternalAccount
}