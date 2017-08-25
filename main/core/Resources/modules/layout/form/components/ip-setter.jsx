import React, {PropTypes as T, Component} from 'react'
import {t} from '#/main/core/translation'

export class IpSetter extends Component {
  constructor(props) {
    super(props)

    this.state = {
      'ip-block-1': '',
      'ip-block-2': '',
      'ip-block-3': '',
      'ip-block-4': ''
    }
  }

  handleChange(event) {
    this.setState({[event.target.id]: event.target.value.toString()})
  }

  addIpFilter() {
    this.props.onChange(this.props.ips.concat([this.getCurrentIp()]))
    this.setState({
      'ip-block-1': '',
      'ip-block-2': '',
      'ip-block-3': '',
      'ip-block-4': ''
    })
  }

  removeAll() {
    this.props.onChange([])
  }

  getCurrentIp() {
    return `${this.state['ip-block-1']}.${this.state['ip-block-2']}.${this.state['ip-block-3']}.${this.state['ip-block-4']}`
  }

  onRemove(ip) {
    this.props.ips.splice(this.props.ips.indexOf(ip), 1)
    this.props.onChange(this.props.ips)
  }

  render() {
    return (
      <div>
        <form className="form-inline">
          <div className="panel panel-body">
            <input
              min="0"
              id="ip-block-1"
              name="ip-block-1"
              max="255"
              className="form-control mb-2 mr-sm-2 mb-sm-0"
              type="number"
              value={this.state['ip-block-1']}
              onChange={this.handleChange.bind(this)}
            />{'\u00A0'}.{'\u00A0'}
            <input
              min="0"
              id="ip-block-2"
              name="ip-block-2"
              max="255"
              className="form-control mb-2 mr-sm-2 mb-sm-0"
              type="number"
              value={this.state['ip-block-2']}
              onChange={this.handleChange.bind(this)}
            />{'\u00A0'}.{'\u00A0'}
            <input
              min="0"
              id="ip-block-3"
              name="ip-block-3"
              max="255"
              className="form-control mb-2 mr-sm-2 mb-sm-0"
              type="number"
              value={this.state['ip-block-3']}
              onChange={this.handleChange.bind(this)}
            />{'\u00A0'}.{'\u00A0'}
            <input
              min="0"
              id="ip-block-4"
              name="ip-block-4"
              max="255"
              className="form-control mb-2 mr-sm-2 mb-sm-0"
              type="number"
              value={this.state['ip-block-4']}
              onChange={this.handleChange.bind(this)}
            />{'\u00A0'}
            <input
              className="btn btn-primary"
              type="button"
              value={t('add_filter')}
              onClick={() => this.addIpFilter()}
            />
          </div>
          <div className="panel panel-body">
            <input
              className="btn btn-danger"
              type="button"
              value={t('remove_all_filter')}
              onClick={() => this.removeAll()}
            />
          </div>
        </form>
        <div>
          {this.props.ips.map(ip => <IPSpan ip={ip} onRemove={this.onRemove.bind(this)} />)}
        </div>
      </div>
    )
  }
}

const IPSpan = props => <div>
  {props.ip}<i className="fa fa-times fa-fw pointer" onClick={() => props.onRemove(props.ip)}/>
</div>

IPSpan.propTypes = {
  ip: T.string.isRequired,
  onRemove: T.func.isRequired
}

IpSetter.propTypes = {
  ips: T.arrayOf(T.string).isRequired,
  onChange: T.func.isRequired
}

IpSetter.defaultProps = {
  ips: []
}
