import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {getMenus} from '#/main/app/layout/header/utils'

class AppearanceMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      headerWidgets: {}
    }
  }

  componentDidMount() {
    getMenus().then(menus => {
      this.setState({headerWidgets: menus.reduce((acc, current) => Object.assign(acc, {
        [current.default.name]: current.default.label
      }), {})})
    })
  }

  render() {
    return (
      <Fragment>
        {false &&
          <header className="row content-heading">
            <ul className="nav nav-tabs">
              <li>
                <Button
                  type={LINK_BUTTON}
                  icon="fa fa-fw fa-desktop"
                  label={trans('user_interface', {}, 'appearance')}
                  target={`${this.props.path}/appearance`}
                  exact={true}
                />
              </li>

              <li>
                <Button
                  type={LINK_BUTTON}
                  icon="fa fa-fw fa-swatchbook"
                  label={trans('themes', {}, 'appearance')}
                  target={`${this.props.path}/appearance/themes`}
                />
              </li>

              <li>
                <Button
                  type={LINK_BUTTON}
                  icon="fa fa-fw fa-icons"
                  label={trans('icons', {}, 'appearance')}
                  target={`${this.props.path}/appearance/icons`}
                />
              </li>

              <li>
                <Button
                  type={LINK_BUTTON}
                  icon="fa fa-fw fa-image"
                  label={trans('posters', {}, 'appearance')}
                  target={`${this.props.path}/appearance/posters`}
                />
              </li>

              <li>
                <Button
                  type={LINK_BUTTON}
                  icon="fa fa-fw fa-palette"
                  label={trans('colors', {}, 'appearance')}
                  target={`${this.props.path}/appearance/colors`}
                />
              </li>
            </ul>
          </header>
        }

        <FormData
          name={selectors.FORM_NAME}
          target={['apiv2_parameters_update']}
          buttons={true}
          cancel={{
            type: LINK_BUTTON,
            target: this.props.path,
            exact: true
          }}
          locked={this.props.lockedParameters}
          sections={[
            {
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'display.resource_icon_set',
                  type: 'choice',
                  label: trans('icons'),
                  required: true,
                  options: {
                    multiple: false,
                    condensed: true,
                    choices: this.props.iconSetChoices
                  }
                }
              ]
            }, {
              icon: 'fa fa-fw fa-heading',
              title: trans('header'),
              fields: [
                {
                  name: 'display.logo',
                  type: 'image',
                  label: trans('logo')
                }, {
                  name: 'display.name_active',
                  type: 'boolean',
                  label: trans('show_name_in_top_bar')
                }, {
                  name: 'header',
                  type: 'choice',
                  label: trans('header_widgets'),
                  options: {
                    inline: false,
                    multiple: true,
                    condensed: false,
                    choices: this.state.headerWidgets
                  }
                }
              ]
            }, {
              icon: 'fa fa-fw fa-map-signs',
              title: trans('breadcrumb'),
              fields: [
                {
                  name: 'display.breadcrumb',
                  type: 'boolean',
                  label: trans('showBreadcrumbs')
                }
              ]
            }, {
              icon: 'fa fa-fw fa-copyright',
              title: trans('footer'),
              fields: [
                {
                  name: 'footer.show_locale',
                  type: 'boolean',
                  label: trans('footer_locale')
                }, {
                  name: 'footer.content',
                  type: 'html',
                  label: trans('footer')
                }
              ]
            }
          ]}
        />
      </Fragment>
    )
  }
}

AppearanceMain.propTypes = {
  path: T.string.isRequired,
  lockedParameters: T.arrayOf(T.string).isRequired,
  iconSetChoices: T.object.isRequired
}

export {
  AppearanceMain
}
