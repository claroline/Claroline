import React, {Component}from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isUndefined from 'lodash/isUndefined'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Password} from '#/main/core/layout/form/components/field/password'
import {ContentHelp} from '#/main/app/content/components/help'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

const Restriction = props => {
  let title, help
  if (props.failed) {
    title = props.fail.title
    help = props.fail.help
  } else {
    title = props.success.title
    help = props.success.help
  }

  return (
    <div className={classes('access-restriction alert alert-detailed', {
      'alert-success': !props.failed,
      'alert-warning': props.failed && props.onlyWarn,
      'alert-danger': props.failed && !props.onlyWarn
    })}>
      <span className={classes('alert-icon', props.icon)} />

      <div className="alert-content">
        <h5 className="alert-title h4">{title}</h5>

        {help &&
          <p className="alert-text">{help}</p>
        }

        {props.failed && props.children}
      </div>
    </div>
  )
}

Restriction.propTypes = {
  icon: T.string.isRequired,
  success: T.shape({
    title: T.string.isRequired,
    help: T.string
  }).isRequired,
  fail: T.shape({
    title: T.string.isRequired,
    help: T.string
  }).isRequired,
  failed: T.bool.isRequired,
  onlyWarn: T.bool, // we only warn for restrictions that can be fixed
  children: T.node
}

Restriction.defaultProps = {
  validated: false,
  onlyWarn: false
}

class ResourceRestrictions extends Component {
  constructor(props) {
    super(props)
    this.state = {
      codeAccess: ''
    }
  }

  updateCodeAccess(value) {
    this.setState({codeAccess: value})
  }

  submitCodeAccess() {
    if (this.state.codeAccess) {
      this.props.checkAccessCode(this.state.codeAccess)
    }
  }

  render() {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-fw fa-lock"
        title={trans('restricted_access')}
        help={trans('restricted_access_message', {}, 'resource')}
      >
        <Restriction
          icon="fa fa-fw fa-id-badge"
          failed={this.props.errors.noRights}
          success={{
            title: this.props.managed ? 'Vous êtes un gestionnaire de la ressource.' : 'Vous pouvez accéder à cette ressource.',
            help: this.props.managed ? 'Vos droits de gestionnaires vous permettents d\'accéder à toutes les fonctions de la ressource' : 'Vos droits vous permettent d\'accéder à une ou plusieurs fonctions de la ressource.'
          }}
          fail={{
            title: 'Vous n\'avez pas les droits nécessaires pour accéder à cette ressource.',
            help: 'Veuillez contacter un gestionnaire si vous pensez que vous devriez avoir accès à cette ressource.'
          }}
        />

        <Restriction
          icon="fa fa-fw fa-eye"
          failed={this.props.errors.deleted || this.props.errors.notPublished}
          success={{
            title: 'La ressource est publiée.',
            help: ''
          }}
          fail={{
            title: this.props.errors.deleted ? 'La ressource est supprimée.' : 'La ressource n\'est pas encore publiée.',
            help: this.props.errors.deleted ? 'Vous ne pouvez plus accéder au contenu des ressources supprimées' : 'Veuillez patienter en attendant que l\'un des gestionnaires publie la ressource.'
          }}
        />

        {(!isUndefined(this.props.errors.notStarted) || !isUndefined(this.props.errors.ended)) &&
          <Restriction
            icon="fa fa-fw fa-calendar"
            failed={this.props.errors.notStarted || this.props.errors.ended}
            success={{
              title: '',
              help: ''
            }}
            fail={{
              title: this.props.errors.notStarted ? 'La ressource n\'est pas encore accessible.' : 'La ressource n\'est plus accessible.',
              help: this.props.errors.notStarted ? 'Veuillez patientez jusqu' : ''
            }}
          />
        }

        {!isUndefined(this.props.errors.locked) &&
          <Restriction
            icon="fa fa-fw fa-key"
            onlyWarn={true}
            failed={this.props.errors.locked}
            success={{
              title: 'Vous avez déverrouillé la ressource.',
              help: ''
            }}
            fail={{
              title: 'L\'accès requiert un code.',
              help: 'Veuillez saisir le code qui vous a été remis afin d\'accéder à la ressource'
            }}
          >
            {this.props.errors.locked &&
              <div>
                <Password
                  id="access-code"
                  value={this.state.codeAccess}
                  onChange={(value) => this.updateCodeAccess(value)}
                />
                <Button
                  className="btn btn-block btn-emphasis"
                  type={CALLBACK_BUTTON}
                  icon="fa fa-fw fa-sign-in-alt"
                  disabled={!this.state.codeAccess}
                  label={trans('Accéder à la ressource', {}, 'actions')}
                  callback={() => this.submitCodeAccess()}
                  primary={true}
                />
              </div>
            }
          </Restriction>
        }

        {!isUndefined(this.props.errors.invalidLocation) &&
          <Restriction
            icon="fa fa-fw fa-laptop"
            onlyWarn={true}
            failed={this.props.errors.invalidLocation}
            success={{
              title: 'Vous utilisez l\'un des postes de de travail authorisé.',
              help: ''
            }}
            fail={{
              title: 'L\'accès doit s\'effectuer depuis un poste de travail authorisé.',
              help: 'Veuillez utiliser l\'un des postes de travail authorisés afin d\'accéder à la ressource.'
            }}
          />
        }

        {this.props.managed &&
          <Button
            className="btn btn-block btn-emphasis"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-sign-in-alt"
            label={trans('Accéder à la ressource', {}, 'actions')}
            callback={this.props.dismiss}
            primary={true}
          />
        }

        {this.props.managed &&
          <ContentHelp
            help="En tant que gestionnaire vous pouvez toujours accéder à la ressource, même si les conditions d'accès ne sont pas satisfaites."
          />
        }
      </EmptyPlaceholder>
    )
  }
}

ResourceRestrictions.propTypes = {
  managed: T.bool,
  errors: T.shape({
    noRights: T.bool.isRequired,
    deleted: T.bool.isRequired,
    notPublished: T.bool.isRequired,
    // optional restrictions (if we get nothing, the restriction is disabled)
    locked: T.bool,
    notStarted: T.bool,
    ended: T.bool,
    invalidLocation: T.bool
  }).isRequired,
  dismiss: T.func.isRequired,
  checkAccessCode: T.func
}

export {
  ResourceRestrictions
}
