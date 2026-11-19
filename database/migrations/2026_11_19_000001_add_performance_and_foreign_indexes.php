<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. associations
        Schema::table('associations', function (Blueprint $table) {
            $table->index('sub_location_id', 'idx_associations_sub_location');
            $table->index(['governorate_id', 'status'], 'idx_associations_gov_status');
            $table->index(['status', 'type'], 'idx_associations_status_type');
        });

        // 2. productive_groups
        Schema::table('productive_groups', function (Blueprint $table) {
            $table->index('sub_location_id', 'idx_prod_groups_sub_location');
            $table->index('group_type', 'idx_prod_groups_type');
            $table->index(['association_id', 'status'], 'idx_prod_groups_assoc_status');
            $table->index(['governorate_id', 'status'], 'idx_prod_groups_gov_status');
        });

        // 3. unified_personnel
        Schema::table('unified_personnel', function (Blueprint $table) {
            $table->index('sub_location_id', 'idx_personnel_sub_location');
            $table->index(['category', 'is_activated_in_field'], 'idx_personnel_cat_activated');
            $table->index(['governorate_id', 'category'], 'idx_personnel_gov_cat');
        });

        // 4. sub_locations
        Schema::table('sub_locations', function (Blueprint $table) {
            $table->index('is_active', 'idx_sub_loc_active');
            $table->index(['uzlah_id', 'is_active'], 'idx_sub_loc_uzlah_active');
        });

        // 5. association_loans
        Schema::table('association_loans', function (Blueprint $table) {
            $table->index('loan_type', 'idx_loans_type');
            $table->index('execution_status', 'idx_loans_execution_status');
            $table->index(['association_id', 'loan_status'], 'idx_loans_assoc_status');
        });

        // 6. association_projects
        Schema::table('association_projects', function (Blueprint $table) {
            $table->index('execution_status', 'idx_projects_exec_status');
            $table->index('project_type', 'idx_projects_type');
            $table->index('uzlah_id', 'idx_projects_uzlah');
        });

        // 7. community_initiatives
        Schema::table('community_initiatives', function (Blueprint $table) {
            $table->index('uzlah_id', 'idx_initiatives_uzlah');
            $table->index('sub_location_id', 'idx_initiatives_sub_loc');
            $table->index('association_id', 'idx_initiatives_assoc');
            $table->index('initiative_type', 'idx_initiatives_type');
        });

        // 8. nurseries
        Schema::table('nurseries', function (Blueprint $table) {
            $table->index('uzlah_id', 'idx_nurseries_uzlah');
            $table->index('status', 'idx_nurseries_status');
            $table->index('ownership_type', 'idx_nurseries_ownership');
        });

        // 9. association_crops
        Schema::table('association_crops', function (Blueprint $table) {
            $table->index('crop_type', 'idx_crops_type');
            $table->index('availability_status', 'idx_crops_avail_status');
            $table->index('execution_status', 'idx_crops_exec_status');
            $table->index('uzlah_id', 'idx_crops_uzlah');
        });

        // 10. farmers_and_lands
        Schema::table('farmers_and_lands', function (Blueprint $table) {
            $table->index('membership_status', 'idx_farmers_membership');
            $table->index('sub_location_id', 'idx_farmers_sub_loc');
        });

        // 11. land_reclamations
        Schema::table('land_reclamations', function (Blueprint $table) {
            $table->index('uzlah_id', 'idx_land_reclamations_uzlah');
            $table->index('sub_location_id', 'idx_land_reclamations_sub_loc');
            $table->index('association_id', 'idx_land_reclamations_assoc');
            $table->index('execution_status', 'idx_land_reclamations_exec_status');
        });

        // 12. dairy_and_livestocks
        Schema::table('dairy_and_livestocks', function (Blueprint $table) {
            $table->index('uzlah_id', 'idx_dairy_uzlah');
            $table->index('sub_location_id', 'idx_dairy_sub_loc');
            $table->index('association_id', 'idx_dairy_assoc');
            $table->index('activity_type', 'idx_dairy_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associations', function (Blueprint $table) {
            $table->dropIndex('idx_associations_sub_location');
            $table->dropIndex('idx_associations_gov_status');
            $table->dropIndex('idx_associations_status_type');
        });

        Schema::table('productive_groups', function (Blueprint $table) {
            $table->dropIndex('idx_prod_groups_sub_location');
            $table->dropIndex('idx_prod_groups_type');
            $table->dropIndex('idx_prod_groups_assoc_status');
            $table->dropIndex('idx_prod_groups_gov_status');
        });

        Schema::table('unified_personnel', function (Blueprint $table) {
            $table->dropIndex('idx_personnel_sub_location');
            $table->dropIndex('idx_personnel_cat_activated');
            $table->dropIndex('idx_personnel_gov_cat');
        });

        Schema::table('sub_locations', function (Blueprint $table) {
            $table->dropIndex('idx_sub_loc_active');
            $table->dropIndex('idx_sub_loc_uzlah_active');
        });

        Schema::table('association_loans', function (Blueprint $table) {
            $table->dropIndex('idx_loans_type');
            $table->dropIndex('idx_loans_execution_status');
            $table->dropIndex('idx_loans_assoc_status');
        });

        Schema::table('association_projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_exec_status');
            $table->dropIndex('idx_projects_type');
            $table->dropIndex('idx_projects_uzlah');
        });

        Schema::table('community_initiatives', function (Blueprint $table) {
            $table->dropIndex('idx_initiatives_uzlah');
            $table->dropIndex('idx_initiatives_sub_loc');
            $table->dropIndex('idx_initiatives_assoc');
            $table->dropIndex('idx_initiatives_type');
        });

        Schema::table('nurseries', function (Blueprint $table) {
            $table->dropIndex('idx_nurseries_uzlah');
            $table->dropIndex('idx_nurseries_status');
            $table->dropIndex('idx_nurseries_ownership');
        });

        Schema::table('association_crops', function (Blueprint $table) {
            $table->dropIndex('idx_crops_type');
            $table->dropIndex('idx_crops_avail_status');
            $table->dropIndex('idx_crops_exec_status');
            $table->dropIndex('idx_crops_uzlah');
        });

        Schema::table('farmers_and_lands', function (Blueprint $table) {
            $table->dropIndex('idx_farmers_membership');
            $table->dropIndex('idx_farmers_sub_loc');
        });

        Schema::table('land_reclamations', function (Blueprint $table) {
            $table->dropIndex('idx_land_reclamations_uzlah');
            $table->dropIndex('idx_land_reclamations_sub_loc');
            $table->dropIndex('idx_land_reclamations_assoc');
            $table->dropIndex('idx_land_reclamations_exec_status');
        });

        Schema::table('dairy_and_livestocks', function (Blueprint $table) {
            $table->dropIndex('idx_dairy_uzlah');
            $table->dropIndex('idx_dairy_sub_loc');
            $table->dropIndex('idx_dairy_assoc');
            $table->dropIndex('idx_dairy_type');
        });
    }
};
