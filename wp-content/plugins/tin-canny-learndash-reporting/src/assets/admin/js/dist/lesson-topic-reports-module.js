window.uotcTopicLessonReports = window.uotcTopicLessonReports || {};

(function ($) {

    class CourseStepReportTable {

        constructor($parent, type) {
            // Parent Element
            this.$parent = $parent;
            // Dom Elements
            this.$elements = {};
            // DataTable Object
            this.table = null;
            // Type of report ( Lessson || Topic ).
            this.type = type;
            // Course IDs & Labels
            this.courseData = [];
            // Course IDs
            this.courseIDs = [];
            // Filter action being performed
            this.action = 'init';
            // Get elements
            this.getElements();
            // Get course data
            this.getCourseData();
            // Listen dropdowns changes
            this.filters();
            // Create DataTable
            this.createDataTable();
        }

        getElements() {
            this.$elements = {
                stepReport: $('table.dataTable', this.$parent),
                filter: {
                    all: $('.uotc-report__filters select', this.$parent),
                    groupID: $('.uotc-report__select__group', this.$parent),
                    courseID: $('.uotc-report__select__course', this.$parent),
                    courseStepID: $('.uotc-report__select__course__step', this.$parent),
                }
            }
        }

        getCourseData() {
            const self = this;
            $("> option", this.$elements.filter.courseID).each(function () {
                let id = this.value !== '' ? parseInt(this.value) : '';
                if (id !== '') {
                    self.courseIDs.push(id);
                }
                self.courseData.push({
                    id: id,
                    text: this.text
                });
            });
        }

        filters() {
            const self = this;
            // Clear table when one of the filters change
            this.$elements.filter.all.on('change', function () {
                // Check if the DataTable is defined
                if (typeof self.$elements.stepReport === 'undefined') {
                    return;
                }

                self.clearTable();
            });

            // Update the courses when the group changes
            this.$elements.filter.groupID.on('change', function () {
                self.$elements.filter.courseID.val('');
                self.$elements.filter.courseStepID.val('');
                self.updateCourseStepSelect([], '');
                if (self.$elements.filter.groupID.val() !== '') {
                    self.updateCourseSelect([], '');
                    self.action = 'update-courses';
                    self.getData();
                } else {
                    self.updateCourseSelect(self.courseIDs, '');
                    self.clearTable();
                }
            });

            // Update the course steps when the course changes
            this.$elements.filter.courseID.on('change', function () {
                self.$elements.filter.courseStepID.val('');
                if (self.$elements.filter.courseID.val() !== '') {
                    self.action = 'update-course-steps';
                    self.getData();
                } else {
                    self.clearTable();
                    self.updateCourseStepSelect([], '');
                }
            });

            // Query Table Data when the course step changes
            this.$elements.filter.courseStepID.on('change', function () {
                self.action = 'update-table';
                if (self.$elements.filter.courseStepID.val() !== '') {
                    self.getData();
                } else {
                    self.clearTable();
                }
            });
        }

        createDataTable(data) {
            let self = this;
            // Set the column Data.
            let columns = [];
            const i18n = uotcTopicLessonReports.i18n;
            for (const c in uotcTopicLessonReports.columns) {
                let column = {
                    data: c,
                    title: uotcTopicLessonReports.columns[c],
                };
                if (c === 'step_status') {
                    column.render = function (data, type, row) {
                        const status = parseInt(data);
                        if (status === 1) {
                            return i18n.status.completed;
                        } else {
                            return i18n.status.incomplete;
                        }
                    }
                }
                columns.push(column);
            }

            // Set the language.
            let language = i18n.table_language;
            language.search = '';
            language.infoPostFix = '';
            language.emptyTable = i18n.emptyTable.defaults[this.type];

            // Set the buttons.
            var buttonCommon = {
                exportOptions: {
                    columns: function (id, data, node) {
                        return self.table.column(id).visible();
                    },
                    format: {
						body: function (data, row, column, node) {
							// Strips hidden timestamp <span>
							return data.replace(/\<span\s(?:class\=\"uotc\-hidden\-data\")(.*)\<\/span\>/g, '');
						}
					}
                },
                filename: function () {
                    let name = 'uo-' + self.type + '-report';
                    if (self.$elements.filter.groupID.val() !== '') {
                        name += '-group-' + $("option:selected", self.$elements.filter.groupID).text();
                    }
                    if (self.$elements.filter.courseID.val() !== '') {
                        name += '-course-' + $("option:selected", self.$elements.filter.courseID).text();
                    }
                    if (self.$elements.filter.courseStepID.val() !== '') {
                        name += '-' + self.type + '-' + $("option:selected", self.$elements.filter.courseStepID).text();
                    }

                    name += '-for-user-id-' + uotcTopicLessonReports.currentUser;

                    name += '-date-' + new Date().toLocaleDateString('en-CA');

                    return name.replace(/\s+/g, '-').toLowerCase();
                }
            };

            // Add Column Filters.
            let initComplete = function (settings, d) {

                let table = settings.oInstance.api();
                let customColumnLabels = i18n.customColumnLabels;

                let $colFilterWrap = $(`<div id="${settings.sInstance}-filter-columns" class="uotc-dataTables-filter-columns">
                    <input class="uotc-dataTables-filter-columns-field" id="${settings.sInstance}-filter-columns-field" type="checkbox">
                    <div class="uotc-dataTables-filter-columns__toggle">
                        <label for="${settings.sInstance}-filter-columns-field" data-label-enable="${customColumnLabels.customizeColumns}" data-label-disable="${customColumnLabels.hideCustomizeColumns}"></label>
                    </div>
                    <div class="uotc-dataTables-filter-columns__fields"></div>
                    </div>`);

                $.each(settings.aoColumns, function (key, value) {
                    const title = settings.aoColumns[key].sTitle;
                    $colFilterWrap.find('.uotc-dataTables-filter-columns__fields').append(self.addColumnCheckbox(title, key, value.bVisible, table));
                });

                $($colFilterWrap).insertBefore(self.$elements.stepReport);

                // Resize the table when search, length or page is performed.
                table.on('search.dt length.dt page.dt', function () {
                    table.columns.adjust().responsive.recalc();
                });
            };


            // Create the DataTable.
            this.table = this.$elements.stepReport.DataTable({
                initComplete: initComplete,
                columns: columns,
                data: data,
                dom: '<"uotc-datatable-header"B>frt<"uotc-datatable-footer"lpi>',
                buttons: [
                    $.extend(true, {}, buttonCommon, {
                        extend: 'csv',
                        text: i18n.buttons.exportCSV,
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'excelHtml5',
                        text: i18n.buttons.exportExcel,
                    }),
                ],
                language: language,
            });
        }

        addColumnCheckbox(label, columnKey, isVisible, table) {
            let $checkbox = $('<input />', {
                type: 'checkbox',
                id: 'dt_' + columnKey,
                value: columnKey,
                checked: isVisible
            });

            $checkbox.on('change', function (e) {

                const currentCheck = $(this).attr('value');
                const isChecked = $(this).is(':checked');

                // Get the column API object
                table.columns().every(function (i) {
                    if (i == currentCheck) {
                        table.column(i).visible(isChecked);
                    }
                });
            });

            return $('<label/>').append($checkbox).append(label);
        }

        clearTable() {
            try {
                // Clear the table
                this.table
                    .clear()
                    .draw()
                    .columns.adjust()
                    .responsive.recalc();
            } catch (e) {
                console.warn(e);
            }
        }

        emptyTableMessage() {

            const i18n = uotcTopicLessonReports.i18n.emptyTable;
            let message = i18n.defaults[this.type];
            if (this.action === 'update-courses') {
                if ($('option', this.$elements.filter.courseID).length === 1) {
                    message = i18n.course;
                }
            } else if (this.action === 'update-course-steps') {
                if ($('option', this.$elements.filter.courseStepID).length === 1) {
                    message = i18n[this.type];
                }
            } else if (this.action === 'update-table') {
                message = i18n.noResults;
            }

            this.action = 'init';
            $('.dataTables_empty', this.$elements.stepReport).html(message);

        }

        populateDataTable(data) {
            // Clear the table
            this.clearTable();

            // Add new data
            this.table.rows.add(data);

            // Redraw the table
            this.table
                .draw()
                .columns.adjust()
                .responsive.recalc();

            // Check if the table is empty
            if (data.length === 0) {
                this.emptyTableMessage();
            }
        }

        updateCourseSelect(courseIDs, courseID) {
            let updatedCourses = [];
            for (const o in this.courseData) {
                const opt = this.courseData[o];
                const id = opt.id;
                if (courseIDs.includes(id)) {
                    updatedCourses.push({
                        id: id,
                        text: opt.text
                    });
                }
            }
            const i18n = uotcTopicLessonReports.i18n.filters.course;
            let emptyMessage = i18n.default;
            if (this.action === 'update-courses' && updatedCourses.length === 0) {
                emptyMessage = i18n.group;
                this.emptyTableMessage();
            }
            this.updateSelect(this.$elements.filter.courseID, updatedCourses, courseID, emptyMessage);

        }

        updateCourseStepSelect(stepIDs, stepID) {
            const i18n = uotcTopicLessonReports.i18n.filters[this.type];
            let emptyMessage = i18n.default;
            if (this.action === 'update-course-steps' && stepIDs.length === 0) {
                emptyMessage = i18n.course;
                this.emptyTableMessage();
            }
            this.updateSelect(this.$elements.filter.courseStepID, stepIDs, stepID, emptyMessage);
        }

        updateSelect($select, ids, value, emptyMessage) {

            let empty = $select.find('option[value=""]');
            if (emptyMessage) {
                empty.text(emptyMessage);
            }
            $select.html('');
            $select.append(empty);
            for (const i in ids) {
                const opt = ids[i];
                const id = opt.id;
                let attrs = {
                    'value': id,
                };
                if (id === value) {
                    attrs.selected = 'selected';
                }
                const option = $('<option/>')
                    .attr(attrs)
                    .text(opt.text);
                $select.append(option);
            }
        }

        getData() {
            const self = this;
            const data = {
                group_id: this.$elements.filter.groupID.val(),
                course_id: this.$elements.filter.courseID.val(),
                course_step_id: this.$elements.filter.courseStepID.val(),
                step_type: this.type,
                user_id: uotcTopicLessonReports.currentUser,
            };

            $.ajax({
                method: "POST",
                data: data,
                url: uotcTopicLessonReports.root + 'get_course_step_data/',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', uotcTopicLessonReports.nonce);
                    self.addLoaderAnimation();
                },
                success: function (response) {

                    if (response.course_ids) {
                        self.updateCourseSelect(response.course_ids, response.course_id);
                    }
                    if (response.step_data) {
                        self.updateCourseStepSelect(response.step_data, response.step_id);
                    }
                    if (response.course_id && response.step_id && response.results) {
                        self.populateDataTable(response.results);
                    }

                    self.removeLoaderAnimation();
                },
                fail: function (response) {
                    alert('Rest Call Failed');
                    console.log(response);
                    self.removeLoaderAnimation();
                }
            });
        }

        addLoaderAnimation() {
            $(this.$parent).append(uotcTopicLessonReports.loadingAnimation);
        }

        removeLoaderAnimation() {
            $('.reporting-status-loading-animation-wrap', this.$parent).remove();
        }
    }


    $(document).ready(function () {

        if (typeof uotcTopicLessonReports !== "undefined") {
            const reportTypes = ['lesson', 'topic'];
            for (let i = 0; i < reportTypes.length; i++) {
                let type = reportTypes[i];
                let $wraps = $('.uotc-' + type + '-report');
                if ($wraps.length) {
                    for (let j = 0; j < $wraps.length; j++) {
                        uotcTopicLessonReports[type + 'Report'] = new CourseStepReportTable($wraps[j], type);
                    }
                }
            }
        }

    });

})(jQuery);