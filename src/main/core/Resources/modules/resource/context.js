import {createContext} from 'react'

const ResourceContext = createContext({
  menu: [],
  actions: [],
  styles: []
})

export {
  ResourceContext
}
