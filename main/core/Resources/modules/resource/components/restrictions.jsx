import React, {Component, Fragment}from 'react'
import {PropTypes as T} from 'prop-types'
import isUndefined from 'lodash/isUndefined'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {PasswordInput} from '#/main/app/data/types/password/components/input'
import {ContentHelp} from '#/main/app/content/components/help'
import {ContentRestriction} from '#/main/app/content/components/restriction'

class ResourceRestrictions extends Component {
  constructor(props) {
    super(props)

    this.state = {
      codeAccess: ''
    }

    this.updateCodeAccess = this.updateCodeAccess.bind(this)
    this.submitCodeAccess = this.submitCodeAccess.bind(this)
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
      <div className="access-restrictions">
        <h2>{trans('restricted_access')}</h2>
        <p>{trans('restricted_access_message', {}, 'resource')}</p>

        <ContentRestriction
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

        <ContentRestriction
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
          <ContentRestriction
            icon="fa fa-fw fa-calendar"
            failed={this.props.errors.notStarted || this.props.errors.ended}
            success={{
              title: '',
              help: ''
            }}
            fail={{
              title: this.props.errors.notStarted ? 'La ressource n\'est pas encore accessible.' : 'La ressource n\'est plus accessible.',
              help: this.props.errors.notStarted ? `Veuillez patientez jusqu'au ${this.props.errors.startDate}` : ''
            }}
          />
        }

        {!isUndefined(this.props.errors.locked) &&
          <ContentRestriction
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
              <Fragment>
                <PasswordInput
                  id="access-code"
                  value={this.state.codeAccess}
                  onChange={this.updateCodeAccess}
                />

                <Button
                  className="btn btn-block btn-emphasis"
                  type={CALLBACK_BUTTON}
                  icon="fa fa-fw fa-sign-in-alt"
                  disabled={!this.state.codeAccess}
                  label={trans('Accéder à la ressource', {}, 'actions')}
                  callback={this.submitCodeAccess}
                  primary={true}
                />
              </Fragment>
            }
          </ContentRestriction>
        }

        {!isUndefined(this.props.errors.invalidLocation) &&
          <ContentRestriction
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
      </div>
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
    invalidLocation: T.bool,
    startDate: T.string
  }).isRequired,
  dismiss: T.func.isRequired,
  checkAccessCode: T.func
}

export {
  ResourceRestrictions
}
