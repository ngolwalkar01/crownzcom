window.uotcGroupQuizReport = window.uotcGroupQuizReport || {};

(function ($) {

	/**
	 * Group Quiz Report Table
	 * 
	 * @param {object} $parent - The parent element.
	 * @param {object} config - The table config.
	 */
	class GroupQuizReportTable {
		constructor($parent, config) {
			this.API = uotcGroupQuizReport;
			// Parent Element
			this.$parent = $parent;
			// Dom Elements
			this.$elements = {};
			// Table Config
			this.tableConfig = config;
			// i18n
			this.i18n = this.API.i18n;
			// Label Loading Class
			this.labelLoadingClass = this.i18n.cssSelectors.labelLoading;
			// Table Loading Selector
			this.tableLoadingSelector = '.' + this.i18n.cssSelectors.tableLoading;
			// DataTable Object
			this.table = null;
			// Score Type
			this.scoreType = config.scoreType;
			// Filter action being performed
			this.action = 'init';
			// Get elements
			this.getElements();
			// Listen dropdowns changes
			this.filters();
		}
		
		getElements() {
			this.$elements = {
				groupQuizReport: $('table.uo-group-quiz-report-table', this.$parent),
				quizSelectLabel: $('.uo-group-quiz-report-quizzes-label', this.$parent),
				filter: {
					all: $('.change-group-management-form', this.$parent),
					group: $('.uo-group-quiz-report-group', this.$parent),
					quiz:  $('.uo-group-quiz-report-quizzes', this.$parent)
				}
			}
		}
		
		filters() {
			const self = this;
			// Clear table when one of the filters change
			this.$elements.filter.all.on('change', function () {
				// Check if the DataTable is defined
				if (typeof self.$elements.groupQuizReport === 'undefined') {
					return;
				}
				self.clearTable();
			});
			
			// Update the quizzes when the group changes
			this.$elements.filter.group.on('change', function () {
				self.$elements.filter.quiz.val('');
				self.action = 'get-quizzes';
				if ( self.$elements.filter.group.val() !== '' ) {
					self.$elements.quizSelectLabel.addClass(self.labelLoadingClass);
					self.getData();
				} else {
					self.clearTable();
					self.updateQuizSelect([ { value: '', text: self.i18n.noQuizResults } ]);
				}
			});
			
			// Query Table Data when the quiz changes
			this.$elements.filter.quiz.on('change', function () {
				self.action = 'update-table';
				if ( self.$elements.filter.quiz.val() !== '' ) {
					self.addLoaderAnimation();
					self.getData();
				} else {
					self.clearTable();
				}
			});
		}
		
		createDataTable(data) {
			let self = this;
			const i18n = self.i18n;
			const tableConfig = self.tableConfig;
			const columnData = tableConfig.columns;
			const configColumns = self.API.columns;
			
			// Set the column Data.
			let columns = [];
			let target  = 0;
			for (const c in configColumns) {
				let column = {
					targets: target,
					data: c,
					title: configColumns[c],
					visible: c !== 'quiz_modal' && columnData.includes( c )
				};
				columns.push(column);
				target++;
			}
			
			// Determin table order.
			let tableOrder = [];
			let tableOrderByIndex = columns.findIndex(columnData => columnData.title == tableConfig.orderBy) || columns.findIndex(columnData => columnData.data == 'quiz_date');
			if (tableOrderByIndex !== -1) {
				tableOrder = [ [tableOrderByIndex, tableConfig.order] ];
			}
			
			// Set the language.
			let language = i18n.table_language;
			
			// Set the buttons.
			let buttonCommon = {
				exportOptions: {
					columns: function (id, data, node) {
						return self.table.column(id).visible();
					},
					format: {
						body: function (data, row, column, node) {
							// Strips hidden timestamp <span>
							return data.replace(/<span class="ulg-hidden-data"[^>]*>.*?<\/span>/g, '');
						}
					}
				},
			};
			
			// Add Column Filters.
			let initComplete = ( settings, d ) => {
				
				const table = settings.oInstance.api();
				const $colFilterWrap = $( `<div id="${settings.sInstance}-filter-columns" class="dataTables-filter-columns">
					<input class="dataTables-filter-columns-field" id="${settings.sInstance}-filter-columns-field" type="checkbox">
					<div class="dataTables-filter-columns__toggle">
						<label for="${settings.sInstance}-filter-columns-field" data-label-enable="${i18n.customColumnLabels.customizeColumns}" data-label-disable="${i18n.customColumnLabels.hideCustomizeColumns}"></label>
					</div>
					<div class="dataTables-filter-columns__fields"></div>
				</div>` );
				
				if (settings.oSavedState !== null) {
					$.each( settings.oSavedState.columns, function ( key, value ){
						const title = settings.aoColumns[key].sTitle;
						$colFilterWrap.find( '.dataTables-filter-columns__fields' ).append( self.addColumnCheckbox( title, key, value.visible, table ) );
					} );
				} else {
					$.each( settings.aoColumns, function ( key, value ){
						const title = settings.aoColumns[key].sTitle;
						$colFilterWrap.find( '.dataTables-filter-columns__fields' ).append( self.addColumnCheckbox( title, key, value.bVisible, table ) );
					} );
				}
				
				$($colFilterWrap).insertBefore(self.$elements.groupQuizReport);
				
				// Resize the table when search, length or page is performed.
				table.on('search.dt length.dt page.dt', function () {
					table.columns.adjust().responsive.recalc();
				});
			};
			
			// Create the DataTable.
			this.table = this.$elements.groupQuizReport.DataTable({
				initComplete: initComplete,
				columns: columns,
				data: data,
				dom: '<"uotc-datatable-header"lB>frt<"uotc-datatable-footer"pi>',
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
				aLengthMenu: [
					[15, 30, 60, -1],
					[15, 30, 60, i18n.all]
				],
				order: tableOrder,
				iDisplayLength: 30,
				columnDefs: [{
					"targets": 5,
					"orderable": false,
					"searchable": false
				}]
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
			if ( this.table === null ) {
				return;
			}
			
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
			//todo add custom messaging if requested
		}
		
		populateDataTable(data) {
			
			// Check if table has been created.
			if ( this.table === null ) {
				this.createDataTable( data );
				this.removeLoaderAnimation();
				return;
			}
			
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
			
			this.removeLoaderAnimation();
		}
		
		updateQuizSelect(options) {
			
			const $select = this.$elements.filter.quiz;
			
			$select.html('');
			
			// Check if there are any options
			if ( options.length === 1 ) {
				// check if only option is empty value
				if ( options[0].value === '' ) {
					// Disable the select
					$select.prop('disabled', true);
					$($select).addClass('uotc-report-select-filter-select--empty');
				}
			} else {
				// Enable the select
				$select.prop('disabled', false);
				$($select).removeClass('uotc-report-select-filter-select--empty');
			}
			
			for (const i in options) {
				const opt = options[i];
				const option = $('<option/>')
					.attr( { 'value': opt.value } )
					.text(opt.text);
				$select.append(option);
			}
			this.$elements.quizSelectLabel.removeClass(this.labelLoadingClass);
		}
		
		getData() {
			const self = this;
			const data = {
				group_id: self.$elements.filter.group.val(),
				quiz_id: self.$elements.filter.quiz.val(),
				score_type: self.scoreType,
			};
			const action = self.action;
			const actionPath = 'get-quizzes' === action ? 'get-quiz-report-quiz-options' : 'get-quiz-report-data';
			const url = self.API.root + actionPath + '/';
			
			$.ajax({
				method: "POST",
				data: data,
				url: url,
				beforeSend: function (xhr) {
					xhr.setRequestHeader('X-WP-Nonce', self.API.nonce);       
				},
				success: function (response) {
					if ( action === 'get-quizzes' ) {
						// Set new options
						self.updateQuizSelect(response.options, '');
					}
					
					if ( action === 'update-table' ) {
						// Update the table
						self.populateDataTable(response.results);
					}					
				},
				fail: function (response) {
					console.log(response);
					self.removeLoaderAnimation();
				}
			});
		}
		
		addLoaderAnimation() {
			$(this.$parent).append(this.API.loadingAnimation);
		}
		
		removeLoaderAnimation() {
			$(this.tableLoadingSelector, this.$parent).remove();
		}
	}
	
	/**
	 * Document Ready load the module.
	 */
	$(document).ready(function () {
		if ( typeof uotcGroupQuizReport !== "undefined" && uotcGroupQuizReport !== null ) {
			const instances = uotcGroupQuizReport.instances;
			const instanceCount = instances.length;
			if ( instanceCount === 0 ) {
				return;
			}
			for (let i = 0; i < instanceCount; i++) {
				const instance = instances[i];
				const $wrap = $('.uo-group-quiz-report[data-instance=' + instance.instance + ']');
				if ( $wrap === null ) {
					continue;
				}
				uotcGroupQuizReport[instance.instance] = new GroupQuizReportTable( $wrap, instance );				
			}
		}
	});

})(jQuery);