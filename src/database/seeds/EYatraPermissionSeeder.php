<?php
namespace Uitoux\EYatra\Database\Seeds;
use App\Permission;
use Illuminate\Database\Seeder;

class EYatraPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [

			//ADMIN MAIN MENUS
			5000 => [
				'display_order' => 100,
				'parent_id' => NULL,
				'name' => 'eyatra',
				'display_name' => 'eYatra',
			],

			//MASTERS
			5080 => [
				'display_order' => 1,
				'parent_id' => 5000,
				'name' => 'eyatra-masters',
				'display_name' => 'Masters',
			],

			//TRIPS
			5001 => [
				'display_order' => 1,
				'parent_id' => 5000,
				'name' => 'trips',
				'display_name' => 'Trips',
			],
			5002 => [
				'display_order' => 1,
				'parent_id' => 5001,
				'name' => 'trip-add',
				'display_name' => 'Add',
			],
			5003 => [
				'display_order' => 2,
				'parent_id' => 5001,
				'name' => 'trip-edit',
				'display_name' => 'Edit',
			],
			5004 => [
				'display_order' => 3,
				'parent_id' => 5001,
				'name' => 'trip-delete',
				'display_name' => 'Delete',
			],
			5005 => [
				'display_order' => 4,
				'parent_id' => 5001,
				'name' => 'view-all-trips',
				'display_name' => 'View All',
			],

			//OUTLETS
			5020 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-outlets',
				'display_name' => 'Outlets',
			],
			5021 => [
				'display_order' => 1,
				'parent_id' => 5020,
				'name' => 'eyatra-outlet-add',
				'display_name' => 'Add',
			],
			5022 => [
				'display_order' => 1,
				'parent_id' => 5020,
				'name' => 'eyatra-outlet-edit',
				'display_name' => 'Edit',
			],
			5023 => [
				'display_order' => 1,
				'parent_id' => 5020,
				'name' => 'eyatra-outlet-delete',
				'display_name' => 'Delete',
			],

			//EMPLOYEES
			5040 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-employees',
				'display_name' => 'Employees',
			],
			5041 => [
				'display_order' => 1,
				'parent_id' => 5040,
				'name' => 'eyatra-employee-add',
				'display_name' => 'Add',
			],
			5042 => [
				'display_order' => 1,
				'parent_id' => 5040,
				'name' => 'eyatra-employee-edit',
				'display_name' => 'Edit',
			],
			5043 => [
				'display_order' => 1,
				'parent_id' => 5040,
				'name' => 'eyatra-employee-delete',
				'display_name' => 'Delete',
			],

			//TRIPS VERIFICATION
			5060 => [
				'display_order' => 1,
				'parent_id' => 5000,
				'name' => 'trips-verification',
				'display_name' => 'Trips Verification',
			],
			5061 => [
				'display_order' => 1,
				'parent_id' => 5001,
				'name' => 'view-all-trip-verifications',
				'display_name' => 'All',
			],

			//MASTERS > AGENTS
			5100 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-agents',
				'display_name' => 'Agents',
			],
			5101 => [
				'display_order' => 1,
				'parent_id' => 5100,
				'name' => 'eyatra-agent-add',
				'display_name' => 'Add',
			],
			5102 => [
				'display_order' => 2,
				'parent_id' => 5100,
				'name' => 'eyatra-agent-edit',
				'display_name' => 'Edit',
			],
			5103 => [
				'display_order' => 3,
				'parent_id' => 5100,
				'name' => 'eyatra-agent-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > GRADES
			5120 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-grades',
				'display_name' => 'Grades',
			],
			5121 => [
				'display_order' => 1,
				'parent_id' => 5120,
				'name' => 'eyatra-grade-add',
				'display_name' => 'Add',
			],
			5122 => [
				'display_order' => 2,
				'parent_id' => 5120,
				'name' => 'eyatra-grade-edit',
				'display_name' => 'Edit',
			],
			5123 => [
				'display_order' => 3,
				'parent_id' => 5120,
				'name' => 'eyatra-grade-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > STATES
			5140 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-states',
				'display_name' => 'States',
			],
			5141 => [
				'display_order' => 1,
				'parent_id' => 5140,
				'name' => 'eyatra-state-add',
				'display_name' => 'Add',
			],
			5142 => [
				'display_order' => 2,
				'parent_id' => 5140,
				'name' => 'eyatra-state-edit',
				'display_name' => 'Edit',
			],
			5143 => [
				'display_order' => 3,
				'parent_id' => 5140,
				'name' => 'eyatra-state-delete',
				'display_name' => 'Delete',
			],

			//TRIPS BOOKING REQUESTS
			5160 => [
				'display_order' => 1,
				'parent_id' => 5000,
				'name' => 'trips-booking-requests',
				'display_name' => 'Trips Booking Requests',
			],
			5161 => [
				'display_order' => 1,
				'parent_id' => 5001,
				'name' => 'view-all-trip-booking-requests',
				'display_name' => 'All',
			],

			//MASTERS > TRAVEL PURPOSES
			5180 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-travel-purposes',
				'display_name' => 'Travel Purposes',
			],
			5181 => [
				'display_order' => 1,
				'parent_id' => 5180,
				'name' => 'eyatra-travel-purposes-add',
				'display_name' => 'Add',
			],
			5182 => [
				'display_order' => 2,
				'parent_id' => 5180,
				'name' => 'eyatra-travel-purposes-edit',
				'display_name' => 'Edit',
			],
			5183 => [
				'display_order' => 3,
				'parent_id' => 5180,
				'name' => 'eyatra-travel-purposes-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > TRAVEL MODES
			5200 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-travel-modes',
				'display_name' => 'Travel Modes',
			],
			5201 => [
				'display_order' => 1,
				'parent_id' => 5200,
				'name' => 'eyatra-travel-modes-add',
				'display_name' => 'Add',
			],
			5202 => [
				'display_order' => 2,
				'parent_id' => 5200,
				'name' => 'eyatra-travel-modes-edit',
				'display_name' => 'Edit',
			],
			5203 => [
				'display_order' => 3,
				'parent_id' => 5200,
				'name' => 'eyatra-travel-modes-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > LOCAL TRAVEL MODES
			5220 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-local-travel-modes',
				'display_name' => 'Local Travel Modes',
			],
			5221 => [
				'display_order' => 1,
				'parent_id' => 5220,
				'name' => 'eyatra-local-travel-modes-add',
				'display_name' => 'Add',
			],
			5222 => [
				'display_order' => 2,
				'parent_id' => 5220,
				'name' => 'eyatra-local-travel-modes-edit',
				'display_name' => 'Edit',
			],
			5223 => [
				'display_order' => 3,
				'parent_id' => 5220,
				'name' => 'eyatra-local-travel-modes-delete',
				'display_name' => 'Delete',
			],

			//AGENT CLAIM
			5240 => [
				'display_order' => 1,
				'parent_id' => 5000,
				'name' => 'agent-claim',
				'display_name' => 'Agent Claim',
			],

			5260 => [
				'display_order' => 1,
				'parent_id' => 5000,
				'name' => 'admin',
				'display_name' => 'Admin Permission',
			],

			//MASTERS > CATEGORIES
			5280 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-category',
				'display_name' => 'Category',
			],
			5281 => [
				'display_order' => 1,
				'parent_id' => 5280,
				'name' => 'eyatra-category-add',
				'display_name' => 'Add',
			],
			5282 => [
				'display_order' => 2,
				'parent_id' => 5280,
				'name' => 'eyatra-category-edit',
				'display_name' => 'Edit',
			],
			5283 => [
				'display_order' => 3,
				'parent_id' => 5280,
				'name' => 'eyatra-category-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > CITIES
			5300 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-cities',
				'display_name' => 'Cities',
			],
			5301 => [
				'display_order' => 1,
				'parent_id' => 5300,
				'name' => 'eyatra-city-add',
				'display_name' => 'Add',
			],
			5302 => [
				'display_order' => 2,
				'parent_id' => 5300,
				'name' => 'eyatra-city-edit',
				'display_name' => 'Edit',
			],
			5303 => [
				'display_order' => 3,
				'parent_id' => 5300,
				'name' => 'eyatra-city-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > DESIGNATIONS
			5320 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-designation',
				'display_name' => 'Designation',
			],
			5321 => [
				'display_order' => 1,
				'parent_id' => 5320,
				'name' => 'eyatra-designation-add',
				'display_name' => 'Add',
			],
			5322 => [
				'display_order' => 2,
				'parent_id' => 5320,
				'name' => 'eyatra-designation-edit',
				'display_name' => 'Edit',
			],
			5323 => [
				'display_order' => 3,
				'parent_id' => 5320,
				'name' => 'eyatra-designation-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > REGIONS
			5340 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-region',
				'display_name' => 'Region',
			],
			5341 => [
				'display_order' => 1,
				'parent_id' => 5340,
				'name' => 'eyatra-region-add',
				'display_name' => 'Add',
			],
			5342 => [
				'display_order' => 2,
				'parent_id' => 5340,
				'name' => 'eyatra-region-edit',
				'display_name' => 'Edit',
			],
			5343 => [
				'display_order' => 3,
				'parent_id' => 5340,
				'name' => 'eyatra-region-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > REJECTION REASONS
			5360 => [
				'display_order' => 1,
				'parent_id' => 5080,
				'name' => 'eyatra-rejection',
				'display_name' => 'Rejection',
			],

			//MASTERS > REJECTION REASONS > TRIP REQUEST REJECTION REASONS
			5380 => [
				'display_order' => 1,
				'parent_id' => 5360,
				'name' => 'eyatra-trip-request-reject',
				'display_name' => 'Trip Request Reject',
			],
			5381 => [
				'display_order' => 1,
				'parent_id' => 5380,
				'name' => 'eyatra-trip-request-reject-add',
				'display_name' => 'Add',
			],
			5382 => [
				'display_order' => 2,
				'parent_id' => 5380,
				'name' => 'eyatra-trip-request-reject-edit',
				'display_name' => 'Edit',
			],
			5383 => [
				'display_order' => 3,
				'parent_id' => 5380,
				'name' => 'eyatra-trip-request-reject-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > REJECTION REASONS > TRIP ADVANCE REJECTION REASONS
			5400 => [
				'display_order' => 1,
				'parent_id' => 5360,
				'name' => 'eyatra-trip-advance-reject',
				'display_name' => 'Trip Advance Reject',
			],
			5401 => [
				'display_order' => 1,
				'parent_id' => 5400,
				'name' => 'eyatra-trip-advance-reject-add',
				'display_name' => 'Add',
			],
			5402 => [
				'display_order' => 2,
				'parent_id' => 5400,
				'name' => 'eyatra-trip-advance-reject-edit',
				'display_name' => 'Edit',
			],
			5403 => [
				'display_order' => 3,
				'parent_id' => 5400,
				'name' => 'eyatra-trip-advance-reject-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > REJECTION REASONS > TRIP CLAIM REJECTION REASONS
			5420 => [
				'display_order' => 1,
				'parent_id' => 5360,
				'name' => 'eyatra-trip-claim-reject',
				'display_name' => 'Trip Claim Reject',
			],
			5421 => [
				'display_order' => 1,
				'parent_id' => 5420,
				'name' => 'eyatra-trip-claim-reject-add',
				'display_name' => 'Add',
			],
			5422 => [
				'display_order' => 2,
				'parent_id' => 5420,
				'name' => 'eyatra-trip-claim-reject-edit',
				'display_name' => 'Edit',
			],
			5423 => [
				'display_order' => 3,
				'parent_id' => 5420,
				'name' => 'eyatra-trip-claim-reject-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > REJECTION REASONS > AGENT CLAIM REJECTION REASONS
			5440 => [
				'display_order' => 1,
				'parent_id' => 5360,
				'name' => 'eyatra-agent-claim-reject',
				'display_name' => 'Trip Claim Reject',
			],
			5441 => [
				'display_order' => 1,
				'parent_id' => 5440,
				'name' => 'eyatra-agent-claim-reject-add',
				'display_name' => 'Add',
			],
			5442 => [
				'display_order' => 2,
				'parent_id' => 5440,
				'name' => 'eyatra-agent-claim-reject-edit',
				'display_name' => 'Edit',
			],
			5443 => [
				'display_order' => 3,
				'parent_id' => 5440,
				'name' => 'eyatra-agent-claim-reject-delete',
				'display_name' => 'Delete',
			],

			//MASTERS > REJECTION REASONS > VOUCHER CLAIM REJECTION REASONS
			5460 => [
				'display_order' => 1,
				'parent_id' => 5360,
				'name' => 'eyatra-voucher-claim-reject',
				'display_name' => 'Voucher Claim Reject',
			],
			5461 => [
				'display_order' => 1,
				'parent_id' => 5460,
				'name' => 'eyatra-voucher-claim-reject-add',
				'display_name' => 'Add',
			],
			5462 => [
				'display_order' => 2,
				'parent_id' => 5460,
				'name' => 'eyatra-voucher-claim-reject-edit',
				'display_name' => 'Edit',
			],
			5463 => [
				'display_order' => 3,
				'parent_id' => 5460,
				'name' => 'eyatra-voucher-claim-reject-delete',
				'display_name' => 'Delete',
			],
		];

		foreach ($permissions as $permission_id => $permsion) {
			$permission = Permission::firstOrNew([
				'id' => $permission_id,
			]);
			$permission->fill($permsion);
			$permission->save();
		}
	}
}