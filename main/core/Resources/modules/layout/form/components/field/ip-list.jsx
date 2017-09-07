import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {HelpBlock} from '#/main/core/layout/form/components/help-block.jsx'
import {Ip} from '#/main/core/layout/form/components/field/ip.jsx'
import {ipDefinition} from '#/main/core/layout/data/types/ip'

class IpList extends Component {
  constructor(props) {
    super(props)

    this.state = {
      pendingIp: ''
    }

    this.addIp         = this.addIp.bind(this)
    this.updateIp      = this.updateIp.bind(this)
    this.updatePending = this.updatePending.bind(this)
    this.removeIp      = this.removeIp.bind(this)
    this.removeAll     = this.removeAll.bind(this)
  }

  addIp() {
    const newIps = this.props.ips.slice()

    newIps.push(this.state.pendingIp)

    this.updatePending('')

    this.props.onChange(newIps)
  }

  updatePending(newIp) {
    this.setState({
      pendingIp: newIp
    })
  }

  updateIp(index, newIp) {
    const newIps = this.props.ips.slice()

    // update
    newIps[index] = newIp

    this.props.onChange(newIps)
  }

  removeIp(index) {
    const newIps = this.props.ips.slice()

    // remove
    newIps.splice(index, 1)

    this.props.onChange(newIps)
  }

  removeAll() {
    this.props.onChange([])
  }

  render() {
    return (
      <div id={this.props.id} className="ip-list-control">
        <div className="ip-item ip-add">
          <Ip
            id={`${this.props.id}-add`}
            size="sm"
            value={this.state.pendingIp}
            onChange={this.updatePending}
          />

          <TooltipButton
            id={`${this.props.id}-add-btn`}
            title={t('add')}
            className="btn-link"
            disabled={!ipDefinition.validate(this.state.pendingIp)}
            onClick={this.addIp}
          >
            <span className="fa fa-fw fa-plus" />
          </TooltipButton>
        </div>

        <HelpBlock help={t('ip_input_help')} />

        {0 !== this.props.ips.length &&
          <button
            type="button"
            className="btn btn-sm btn-link-danger"
            onClick={this.removeAll}
          >
            {t('delete_all')}
          </button>
        }

        {0 !== this.props.ips.length &&
          <ul>
            {this.props.ips.map((ip, index) =>
              <li key={`${this.props.id}-${index}`} className="ip-item">
                <Ip
                  id={`${this.props.id}-auth-${index}`}
                  size="sm"
                  placeholder=""
                  value={ip}
                  onChange={ip => this.updateIp(index, ip)}
                />

                <TooltipButton
                  id={`${this.props.id}-auth-${index}-delete`}
                  title={t('delete')}
                  className="btn-link-danger"
                  onClick={() => this.removeIp(index)}
                >
                  <span className="fa fa-fw fa-trash-o" />
                </TooltipButton>
              </li>
            )}
          </ul>
        }

        {0 === this.props.ips.length &&
          <div className="no-ip-info">{this.props.emptyText}</div>
        }
      </div>
    )
  }
}

IpList.propTypes = {
  id: T.string.isRequired,
  ips: T.arrayOf(T.string).isRequired,
  onChange: T.func.isRequired,
  emptyText: T.string
}

IpList.defaultProps = {
  emptyText: t('no_ip')
}

export {
  IpList
}
