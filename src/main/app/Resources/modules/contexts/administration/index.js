import {declareContext} from '#/main/app/context'
import {AdministrationContext} from '#/main/app/contexts/administration/components/context'

export default declareContext('administration', '/administration', AdministrationContext)
