# TODO List for Linking Subscription Tiers to Permissions and APIs

## Tasks

- [ ] Create migration for pivot table "permission_tier" linking tiers and permissions.
- [ ] Update Tier model to add many-to-many relationship with Permission.
- [ ] Update Permission model to add inverse many-to-many relationship with Tier.
- [ ] Extend PermissionsService or create new service to manage tier-permission assignments.
- [ ] Create PermissionTierController with APIs:
  - [ ] POST /tiers/{tier}/permissions - Assign permissions to tier.
  - [ ] DELETE /tiers/{tier}/permissions/{permission} - Remove permission from tier.
  - [ ] GET /tiers/{tier}/permissions - List permissions for tier.
- [ ] Add API routes for the above APIs in routes/api.php with appropriate middleware and prefix.
- [ ] Extend user permission fetching APIs to include permissions unlocked via active subscription tiers.
- [ ] Add Swagger/OpenAPI documentation for new APIs.
- [ ] Write unit and feature tests for the new functionality and APIs.
- [ ] Manually or automatically test to validate functionality, routing, and access control.


## Next Step: 
Start by creating migration for pivot table "permission_tier".
