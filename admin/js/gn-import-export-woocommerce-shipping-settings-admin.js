(function( $ ) {
	'use strict';

	var config = window.gnIeWcssAdmin || {};
	var i18n = config.i18n || {};

	function text( key, fallback ) {
		if ( Object.prototype.hasOwnProperty.call( i18n, key ) ) {
			return i18n[ key ];
		}

		return fallback;
	}

	function escapeHtml( value ) {
		return String( value ).replace( /[&<>"']/g, function( match ) {
			switch ( match ) {
				case '&':
					return '&amp;';
				case '<':
					return '&lt;';
				case '>':
					return '&gt;';
				case '"':
					return '&quot;';
				case '\'':
					return '&#039;';
				default:
					return match;
			}
		} );
	}

	function getStatusLabel( status ) {
		if ( 'different' === status ) {
			return text( 'statusDifferent', 'Different' );
		}

		if ( 'missing_table' === status ) {
			return text( 'statusMissing', 'Missing table' );
		}

		return text( 'statusMatch', 'Match' );
	}

	function setPreviewStatus( message, state ) {
		var statusNode = $( '#gn_ie_wcss_preview_status' );

		if ( ! statusNode.length ) {
			return;
		}

		statusNode.removeClass( 'is-loading is-error is-success' );
		if ( 'loading' === state ) {
			statusNode.addClass( 'is-loading' );
		} else if ( 'error' === state ) {
			statusNode.addClass( 'is-error' );
		} else if ( 'success' === state ) {
			statusNode.addClass( 'is-success' );
		}

		statusNode.text( message );
	}

	function renderSampleRows( rows ) {
		var html = '';

		if ( ! Array.isArray( rows ) || 0 === rows.length ) {
			return '<p class="description">' + escapeHtml( text( 'noRowsLabel', 'No rows available.' ) ) + '</p>';
		}

		rows.forEach( function( row, index ) {
			var columns = Object.keys( row || {} );

			html += '<div class="gn-ie-wcss-sample-row">';
			html += '<h5 class="gn-ie-wcss-sample-title">#' + ( index + 1 ) + '</h5>';
			html += '<table class="widefat striped gn-ie-wcss-sample-table"><tbody>';

			columns.forEach( function( columnName ) {
				html += '<tr>';
				html += '<th>' + escapeHtml( columnName ) + '</th>';
				html += '<td><code>' + escapeHtml( row[ columnName ] ) + '</code></td>';
				html += '</tr>';
			} );

			html += '</tbody></table>';
			html += '</div>';
		} );

		return html;
	}

	function renderSnapshot( targetSelector, snapshot, contextClass ) {
		var container = $( targetSelector );
		var html = '';

		if ( ! container.length ) {
			return;
		}

		if ( ! snapshot || ! Array.isArray( snapshot.tables ) ) {
			container.html( '<p class="description">' + escapeHtml( text( 'previewStatusError', 'Could not load preview data.' ) ) + '</p>' );
			return;
		}

		html += '<p class="gn-ie-wcss-prefix">';
		html += '<strong>' + escapeHtml( text( 'prefixLabel', 'DB Prefix' ) ) + ':</strong> ';
		html += '<code>' + escapeHtml( snapshot.prefix || text( 'prefixNotFound', '(not found)' ) ) + '</code>';
		html += '</p>';

		snapshot.tables.forEach( function( tableInfo ) {
			var tableClass = 'gn-ie-wcss-table gn-ie-wcss-table-' + contextClass;
			var tableExists = !! tableInfo.exists;
			var count = Number( tableInfo.count || 0 );

			if ( ! tableExists && 'destination' === contextClass ) {
				tableClass += ' gn-ie-wcss-table-missing';
			}

			html += '<div class="' + tableClass + '" data-table-key="' + escapeHtml( tableInfo.key || '' ) + '">';
			html += '<div class="gn-ie-wcss-table-head">';
			html += '<h4>' + escapeHtml( tableInfo.label || '' ) + '</h4>';
			html += '<span class="gn-ie-wcss-table-count">' + escapeHtml( text( 'rowsLabel', 'Rows' ) ) + ': <strong>' + escapeHtml( count ) + '</strong></span>';
			html += '</div>';
			html += '<p class="gn-ie-wcss-table-name"><strong>' + escapeHtml( text( 'tableLabel', 'Detected table' ) ) + ':</strong> <code>' + escapeHtml( tableInfo.table_name || '' ) + '</code></p>';

			if ( ! tableExists && 'destination' === contextClass ) {
				html += '<p class="gn-ie-wcss-table-missing-text">' + escapeHtml( text( 'missingTableLabel', 'Table does not exist on destination.' ) ) + '</p>';
			}

			html += renderSampleRows( tableInfo.sample_rows || [] );
			html += '</div>';
		} );

		container.html( html );
	}

	function renderComparisonTable( rows ) {
		var container = $( '#gn_ie_wcss_comparison_preview' );
		var html = '';

		if ( ! container.length ) {
			return;
		}

		if ( ! Array.isArray( rows ) || 0 === rows.length ) {
			container.html( '<p class="description">' + escapeHtml( text( 'previewStatusIdle', 'Select a dump file and click "Analyze Dump Preview".' ) ) + '</p>' );
			return;
		}

		html += '<table class="widefat striped gn-ie-wcss-comparison-table">';
		html += '<thead><tr>';
		html += '<th>' + escapeHtml( text( 'tableHeader', 'Table' ) ) + '</th>';
		html += '<th>' + escapeHtml( text( 'sourceHeader', 'Source' ) ) + '</th>';
		html += '<th>' + escapeHtml( text( 'destinationHeader', 'Destination' ) ) + '</th>';
		html += '<th>' + escapeHtml( text( 'statusHeader', 'Status' ) ) + '</th>';
		html += '</tr></thead><tbody>';

		rows.forEach( function( row ) {
			var statusClass = 'gn-ie-wcss-status-' + ( row.status || 'match' );

			html += '<tr class="' + statusClass + '">';
			html += '<td>' + escapeHtml( row.label || '' ) + '</td>';
			html += '<td>' + escapeHtml( row.source_count || 0 ) + '</td>';
			html += '<td>' + escapeHtml( row.destination_count || 0 ) + '</td>';
			html += '<td><span class="gn-ie-wcss-status-badge">' + escapeHtml( getStatusLabel( row.status ) ) + '</span></td>';
			html += '</tr>';
		} );

		html += '</tbody></table>';
		container.html( html );
	}

	function getAjaxErrorMessage( responseJson ) {
		if ( responseJson && responseJson.data && responseJson.data.message ) {
			return responseJson.data.message;
		}

		return text( 'previewStatusError', 'Could not load preview data.' );
	}

	$( function() {
		var previewButton = $( '#gn_ie_wcss_preview_button' );
		var fileInput = $( '#gn_ie_wcss_dump_file' );

		renderSnapshot( '#gn_ie_wcss_destination_preview', config.destinationSnapshot || {}, 'destination' );
		$( '#gn_ie_wcss_source_preview' ).html( '<p class="description">' + escapeHtml( text( 'previewStatusIdle', 'Select a dump file and click "Analyze Dump Preview".' ) ) + '</p>' );
		renderComparisonTable( [] );
		setPreviewStatus( text( 'previewStatusIdle', 'Select a dump file and click "Analyze Dump Preview".' ), 'idle' );

		previewButton.on( 'click', function() {
			var formData;
			var selectedFile;

			if ( ! fileInput.length || ! fileInput[ 0 ].files || ! fileInput[ 0 ].files.length ) {
				setPreviewStatus( text( 'selectFileError', 'Please select a dump file first.' ), 'error' );
				return;
			}

			selectedFile = fileInput[ 0 ].files[ 0 ];
			formData = new FormData();
			formData.append( 'action', config.previewAction || 'gn_ie_wcss_preview_dump' );
			formData.append( 'nonce', config.previewNonce || '' );
			formData.append( 'dump_file', selectedFile );

			previewButton.prop( 'disabled', true );
			setPreviewStatus( text( 'previewStatusLoading', 'Analyzing dump file...' ), 'loading' );

			$.ajax( {
				url: config.ajaxUrl || window.ajaxurl,
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json'
			} )
				.done( function( response ) {
					if ( ! response || ! response.success || ! response.data ) {
						setPreviewStatus( getAjaxErrorMessage( response ), 'error' );
						return;
					}

					renderSnapshot( '#gn_ie_wcss_source_preview', response.data.source || {}, 'source' );
					renderSnapshot( '#gn_ie_wcss_destination_preview', response.data.destination || {}, 'destination' );
					renderComparisonTable( response.data.comparison || [] );
					setPreviewStatus( text( 'previewStatusReady', 'Preview updated. Review source and destination differences below.' ), 'success' );
				} )
				.fail( function( jqXHR ) {
					var responseJson = jqXHR && jqXHR.responseJSON ? jqXHR.responseJSON : null;
					setPreviewStatus( getAjaxErrorMessage( responseJson ), 'error' );
				} )
				.always( function() {
					previewButton.prop( 'disabled', false );
				} );
		} );
	} );
})( jQuery );
