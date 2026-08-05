<style>
	@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

	/* ============ Design tokens (scoped to this view only) ============ */
	.announcements-view {
		--ann-cyan: #00C5FB;
		--ann-primary: #0253CC;
		--ann-primary-dark: #013E99;
		--ann-primary-light: #E6F7FF;
		--ann-accent: #14B8A6;
		--ann-accent-light: #E6FBF7;
		--ann-warning: #F59E0B;
		--ann-warning-light: #FEF3E2;
		--ann-danger: #F43F5E;
		--ann-danger-light: #FFE9EC;
		--ann-ink: #14213D;
		--ann-muted: #64748B;
		--ann-bg: #F4F9FF;
		--ann-border: #E3EEF9;
		--ann-white: #FFFFFF;
		--ann-radius: 16px;
		--ann-shadow: 0 4px 24px rgba(2, 83, 204, 0.1);
		--ann-shadow-lg: 0 24px 70px rgba(2, 40, 99, 0.2);
		--ann-glow: 0 0 0 4px rgba(0, 197, 251, 0.15);
		font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		color: var(--ann-ink);
		-webkit-font-smoothing: antialiased;
	}

	.announcements-view h3,
	.announcements-view h4,
	.announcements-view .ann-heading,
	.announcements-view .btn {
		font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
	}

	.announcements-view * {
		box-sizing: border-box;
	}

	.announcements-view .content.container-fluid {
		background: var(--ann-bg);
		padding-top: 24px;
		padding-bottom: 40px;
	}

	/* ============ Header ============ */
	.ann-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 20px;
		flex-wrap: wrap;
		margin-bottom: 22px;
	}

	.ann-header-left {
		display: flex;
		align-items: center;
		gap: 16px;
		min-width: 0;
	}

	.ann-header-icon {
		position: relative;
		width: 48px;
		height: 48px;
		min-width: 48px;
		border-radius: 14px;
		background: -webkit-linear-gradient(left, var(--ann-cyan) 0%, var(--ann-primary) 100%);
		background: linear-gradient(120deg, var(--ann-cyan), var(--ann-primary), var(--ann-cyan));
		background-size: 220% 220%;
		animation: annGradientShift 5s ease infinite;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #fff;
		font-size: 18px;
		box-shadow: 0 8px 20px rgba(2, 83, 204, .35);
	}

	.ann-header-icon .ann-ring {
		position: absolute;
		inset: 0;
		border-radius: 14px;
		border: 2px solid rgba(2, 83, 204, .4);
		animation: annRing 3s ease-out infinite;
	}

	.ann-page-title {
		position: relative;
		margin: 0;
		font-size: 20px;
		font-weight: 800;
		color: var(--ann-ink);
		letter-spacing: -.01em;
		display: inline-block;
	}

	.ann-page-title::after {
		content: '';
		display: block;
		width: 34px;
		height: 3px;
		border-radius: 3px;
		margin-top: 5px;
		background: linear-gradient(90deg, var(--ann-cyan), var(--ann-primary));
	}

	.ann-page-subtitle {
		margin: 2px 0 0;
		font-size: 13px;
		color: var(--ann-muted);
	}

	.ann-btn-primary {
		position: relative;
		display: inline-flex;
		align-items: center;
		gap: 9px;
		padding: 11px 20px;
		background: -webkit-linear-gradient(left, var(--ann-cyan) 0%, var(--ann-primary) 100%);
		background: linear-gradient(90deg, var(--ann-cyan), var(--ann-primary));
		color: #fff !important;
		font-weight: 700;
		font-size: 13.5px;
		border-radius: 11px;
		border: none;
		text-decoration: none !important;
		box-shadow: 0 8px 18px rgba(2, 83, 204, .28);
		transition: transform .15s ease, box-shadow .15s ease;
		white-space: nowrap;
		cursor: pointer;
		overflow: hidden;
		isolation: isolate;
	}

	.ann-btn-primary::before {
		content: '';
		position: absolute;
		inset: 0;
		background: linear-gradient(115deg, transparent 20%, rgba(255, 255, 255, .35) 40%, transparent 60%);
		transform: translateX(-120%);
		transition: transform .6s ease;
	}

	.ann-btn-primary:hover::before {
		transform: translateX(120%);
	}

	.ann-btn-primary:hover,
	.ann-btn-primary:focus {
		transform: translateY(-2px);
		box-shadow: 0 14px 28px rgba(2, 83, 204, .4), var(--ann-glow);
		color: #fff;
	}

	.ann-btn-primary .ann-btn-icon {
		width: 20px;
		height: 20px;
		border-radius: 50%;
		background: rgba(255, 255, 255, .22);
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 10px;
	}

	.ann-btn-ghost {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 10px 16px;
		background: #fff;
		color: var(--ann-ink) !important;
		font-weight: 600;
		font-size: 13.5px;
		border-radius: 11px;
		border: 1.5px solid var(--ann-border);
		text-decoration: none !important;
		cursor: pointer;
		transition: border-color .15s ease, background .15s ease;
	}

	.ann-btn-ghost:hover {
		border-color: var(--ann-primary);
		background: var(--ann-primary-light);
		color: var(--ann-primary-dark) !important;
	}

	/* ============ View switch (List / Add / Edit "pages") ============ */
	.ann-crumb {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 18px;
		font-size: 13px;
		color: var(--ann-muted);
		font-weight: 600;
	}

	.ann-crumb .ann-crumb-current {
		color: var(--ann-primary-dark);
	}

	.ann-crumb i {
		font-size: 10px;
		opacity: .6;
	}

	/* ============ Stats strip ============ */
	.ann-stats {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 14px;
		margin-bottom: 18px;
	}

	.ann-stat-card {
		position: relative;
		background: var(--ann-white);
		border: 1px solid var(--ann-border);
		border-radius: 14px;
		padding: 16px 18px;
		display: flex;
		align-items: center;
		gap: 12px;
		overflow: hidden;
		box-shadow: 0 2px 12px rgba(2, 83, 204, .05);
		transition: transform .18s ease, box-shadow .18s ease;
	}

	.ann-stat-card:hover {
		transform: translateY(-3px);
		box-shadow: 0 12px 24px rgba(2, 83, 204, .12);
	}

	.ann-stat-icon {
		width: 38px;
		height: 38px;
		min-width: 38px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 15px;
		color: #fff;
	}

	.ann-stat-icon.ann-stat-total {
		background: linear-gradient(135deg, var(--ann-cyan), var(--ann-primary));
	}

	.ann-stat-icon.ann-stat-active {
		background: linear-gradient(135deg, #2DD4BF, #0B8272);
	}

	.ann-stat-icon.ann-stat-draft {
		background: linear-gradient(135deg, #C7CBE0, #8B90AC);
	}

	.ann-stat-icon.ann-stat-urgent {
		background: linear-gradient(135deg, #FB7185, var(--ann-danger));
	}

	.ann-stat-value {
		font-size: 20px;
		font-weight: 800;
		font-family: 'Plus Jakarta Sans', sans-serif;
		color: var(--ann-ink);
		line-height: 1.1;
	}

	.ann-stat-label {
		font-size: 11.5px;
		font-weight: 600;
		color: var(--ann-muted);
		text-transform: uppercase;
		letter-spacing: .04em;
	}

	@media (max-width: 768px) {
		.ann-stats {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	/* ============ Table card ============ */
	.ann-table-card {
		position: relative;
		background: var(--ann-white);
		border-radius: var(--ann-radius);
		box-shadow: var(--ann-shadow);
		border: 1px solid var(--ann-border);
		overflow: hidden;
	}

	.ann-table-card,
	.ann-form-card {
		animation: annFadeInUp .4s ease both;
	}

	.ann-table-card::before,
	.ann-form-card::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, var(--ann-cyan), var(--ann-primary));
		z-index: 1;
	}

	.announcements-view .table-responsive {
		border: none;
		margin: 0;
	}

	.announcements-view table.custom-table {
		margin: 0;
		border-collapse: separate;
		border-spacing: 0;
	}

	.announcements-view table.custom-table thead th {
		background: #FBFBFE;
		border-bottom: 1px solid var(--ann-border);
		border-top: none;
		color: var(--ann-muted);
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .06em;
		padding: 14px 16px;
		white-space: nowrap;
	}

	.announcements-view table.custom-table tbody tr {
		position: relative;
		transition: background .15s ease;
	}

	/*
	.announcements-view table.custom-table tbody tr::before {
		content: '';
		position: absolute;
		left: 0;
		top: 0;
		bottom: 0;
		width: 3px;
		background: linear-gradient(180deg, var(--ann-cyan), var(--ann-primary));
		transform: scaleY(0);
		transition: transform .18s ease;
		transform-origin: center;
	} */

	.announcements-view table.custom-table.table-striped tbody tr:nth-of-type(odd) {
		background: transparent;
	}

	.announcements-view table.custom-table tbody tr:hover {
		background: var(--ann-primary-light);
	}

	.announcements-view table.custom-table tbody tr:hover::before {
		transform: scaleY(1);
	}

	.announcements-view table.custom-table td {
		padding: 13px 16px;
		border-top: 1px solid var(--ann-border);
		vertical-align: middle;
		font-size: 13.5px;
	}

	.ann-title-text {
		font-weight: 700;
		font-size: 14px;
		color: var(--ann-ink);
		margin: 0;
		max-width: 280px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.ann-views {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-weight: 600;
		color: var(--ann-muted);
		font-variant-numeric: tabular-nums;
	}

	.ann-views-btn {
		background: none;
		border: none;
		padding: 4px 8px;
		border-radius: 8px;
		cursor: pointer;
		transition: background .15s ease, color .15s ease;
	}

	.ann-views-btn:hover {
		background: var(--ann-primary-light);
		color: var(--ann-primary-dark);
	}

	/* Hide Angular-controlled elements until Angular has compiled the page —
	   prevents the modal from flashing open for a split second on page load. */
	.announcements-view [ng-cloak] {
		display: none !important;
	}

	/* ============ Viewers modal ============ */
	.ann-modal-backdrop {
		position: fixed;
		inset: 0;
		background: rgba(20, 33, 61, .55);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 1000;
		padding: 20px;
		animation: annFadeInUp .2s ease both;
	}

	.ann-modal {
		background: var(--ann-white);
		border-radius: var(--ann-radius);
		width: 100%;
		max-width: 460px;
		max-height: 80vh;
		display: flex;
		flex-direction: column;
		overflow: hidden;
		box-shadow: var(--ann-shadow-lg);
	}

	.ann-modal-header {
		background: linear-gradient(90deg, var(--ann-cyan), var(--ann-primary));
		background: -webkit-linear-gradient(left, var(--ann-cyan) 0%, var(--ann-primary) 100%);
		padding: 18px 22px;
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 12px;
	}

	.ann-modal-header h4 {
		color: #fff;
		font-weight: 700;
		font-size: 15px;
		margin: 0 0 4px;
	}

	.ann-modal-header p {
		color: rgba(255, 255, 255, .85);
		font-size: 12.5px;
		margin: 0;
	}

	.ann-modal-close {
		background: rgba(255, 255, 255, .2);
		border: none;
		color: #fff;
		width: 28px;
		height: 28px;
		min-width: 28px;
		border-radius: 50%;
		cursor: pointer;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 13px;
	}

	.ann-modal-close:hover {
		background: rgba(255, 255, 255, .32);
	}

	.ann-modal-body {
		padding: 8px 0;
		overflow-y: auto;
	}

	.ann-viewer-row {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 11px 22px;
		border-bottom: 1px solid var(--ann-border);
	}

	.ann-viewer-row:last-child {
		border-bottom: none;
	}

	.ann-viewer-avatar {
		width: 34px;
		height: 34px;
		min-width: 34px;
		border-radius: 50%;
		background: var(--ann-primary-light);
		color: var(--ann-primary-dark);
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: 700;
		font-size: 13px;
	}

	.ann-viewer-name {
		font-weight: 700;
		font-size: 13.5px;
		color: var(--ann-ink);
		margin: 0;
	}

	.ann-viewer-meta {
		font-size: 11.5px;
		color: var(--ann-muted);
		margin: 2px 0 0;
	}

	.ann-viewer-time {
		margin-left: auto;
		text-align: right;
		font-size: 11.5px;
		color: var(--ann-muted);
		white-space: nowrap;
	}

	.ann-modal-loading,
	.ann-modal-empty {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 40px 22px;
		color: var(--ann-muted);
	}

	.ann-modal-loading i,
	.ann-modal-empty i {
		font-size: 22px;
		margin-bottom: 10px;
		color: var(--ann-primary);
		opacity: .6;
	}

	/* ============ Badges / pills ============ */
	.ann-badge {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 4px 11px;
		border-radius: 999px;
		font-size: 11px;
		font-weight: 700;
		letter-spacing: .02em;
		white-space: nowrap;
	}

	.ann-badge-dot {
		width: 6px;
		height: 6px;
		border-radius: 50%;
		background: currentColor;
	}

	.ann-badge-urgent {
		background: var(--ann-danger-light);
		color: var(--ann-danger);
		animation: annUrgentPulse 2.2s ease-in-out infinite;
	}

	.ann-badge-important {
		background: var(--ann-warning-light);
		color: #B4740E;
	}

	.ann-badge-normal {
		background: var(--ann-primary-light);
		color: var(--ann-primary-dark);
	}

	.ann-badge-active {
		background: var(--ann-accent-light);
		color: #0B8272;
	}

	.ann-badge-closed {
		background: #F1F1F6;
		color: var(--ann-muted);
	}

	.ann-badge-draft {
		background: var(--ann-primary-light);
		color: var(--ann-primary-dark);
	}

	.ann-badge-push {
		background: var(--ann-warning-light);
		color: #B4740E;
	}

	.ann-badge-sent {
		background: var(--ann-accent-light);
		color: #0B8272;
	}

	/* ============ Direct row action buttons (no dropdown) ============ */
	.ann-row-actions {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 6px;
		flex-wrap: nowrap;
	}

	.ann-icon-btn {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		height: 32px;
		padding: 0 12px;
		border-radius: 9px;
		font-size: 12.5px;
		font-weight: 700;
		border: 1.5px solid var(--ann-border);
		background: #fff;
		color: var(--ann-muted);
		cursor: pointer;
		transition: all .15s ease;
		white-space: nowrap;
	}

	.ann-icon-btn.ann-edit-btn:hover {
		border-color: var(--ann-primary);
		background: var(--ann-primary-light);
		color: var(--ann-primary-dark);
	}

	.ann-icon-btn.ann-delete-btn {
		border-color: var(--ann-danger);
		background: var(--ann-danger-light);
		color: var(--ann-danger);
	}

	.ann-icon-btn.ann-delete-btn:hover {
		border-color: var(--ann-danger);
		background: var(--ann-danger);
		color: #fff;
	}

	/* Inline delete confirmation strip that replaces the row's action cell */
	.ann-confirm-strip {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 8px;
		flex-wrap: nowrap;
	}

	.ann-confirm-strip span {
		font-size: 12px;
		font-weight: 700;
		color: var(--ann-danger);
		white-space: nowrap;
	}

	.ann-confirm-yes {
		height: 30px;
		padding: 0 12px;
		border-radius: 8px;
		border: none;
		background: var(--ann-danger);
		color: #fff;
		font-weight: 700;
		font-size: 12px;
		cursor: pointer;
	}

	.ann-confirm-no {
		height: 30px;
		padding: 0 12px;
		border-radius: 8px;
		border: 1.5px solid var(--ann-border);
		background: #fff;
		color: var(--ann-ink);
		font-weight: 700;
		font-size: 12px;
		cursor: pointer;
	}

	/* ============ Empty state ============ */
	.ann-empty-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 60px 24px;
	}

	.ann-empty-icon {
		width: 60px;
		height: 60px;
		border-radius: 50%;
		background: var(--ann-primary-light);
		color: var(--ann-primary);
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 22px;
		margin-bottom: 14px;
		animation: annBounceIn .5s ease both;
	}

	.ann-empty-state h5 {
		font-family: 'Plus Jakarta Sans', sans-serif;
		font-weight: 700;
		margin: 0 0 6px;
		color: var(--ann-ink);
	}

	.ann-empty-state p {
		color: var(--ann-muted);
		font-size: 13px;
		margin: 0;
	}

	/* ============ Inline form "page" (replaces modal) ============ */
	.ann-form-card {
		position: relative;
		background: var(--ann-white);
		border-radius: var(--ann-radius);
		box-shadow: var(--ann-shadow);
		border: 1px solid var(--ann-border);
		overflow: hidden;
	}

	.ann-form-card-header {
		background: -webkit-linear-gradient(left, var(--ann-cyan) 0%, var(--ann-primary) 100%);
		background: linear-gradient(90deg, var(--ann-cyan), var(--ann-primary));
		padding: 18px 24px;
	}

	.ann-form-card-header h4 {
		color: #fff;
		font-weight: 700;
		font-size: 16px;
		margin: 0;
	}

	.ann-form-card-body {
		padding: 24px;
	}

	.announcements-view .ann-form-card-body h3 {
		font-size: 14px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .04em;
		color: var(--ann-primary-dark);
		margin: 0 0 16px;
	}

	.announcements-view .form-group label {
		font-weight: 600;
		font-size: 13px;
		color: var(--ann-ink);
		margin-bottom: 6px;
	}

	.announcements-view .form-control,
	.announcements-view .select2-container--default .select2-selection--multiple {
		border: 1.5px solid var(--ann-border);
		border-radius: 10px;
		box-shadow: none;
		font-size: 14px;
		transition: border-color .15s ease, box-shadow .15s ease;
	}

	.announcements-view .form-control:focus,
	.announcements-view .select2-container--focus .select2-selection--multiple {
		border-color: var(--ann-primary);
		box-shadow: 0 0 0 3px var(--ann-primary-light);
	}

	.announcements-view textarea.form-control {
		border-radius: 12px;
	}

	.announcements-view hr {
		border-top: 1px solid var(--ann-border);
		margin: 20px 0;
	}

	.announcements-view .checkbox label {
		font-weight: 500;
		font-size: 13.5px;
	}

	.announcements-view .ann-form-actions {
		border-top: 1px solid var(--ann-border);
		margin-top: 22px !important;
		padding-top: 20px;
		display: flex;
		gap: 12px;
	}

	.announcements-view .ann-form-actions .btn-primary {
		background: -webkit-linear-gradient(left, var(--ann-cyan) 0%, var(--ann-primary) 100%);
		background: linear-gradient(90deg, var(--ann-cyan), var(--ann-primary));
		border: none;
		border-radius: 12px;
		padding: 12px 28px;
		font-weight: 700;
		box-shadow: 0 10px 22px rgba(2, 83, 204, .3);
		transition: transform .18s ease, box-shadow .18s ease;
	}

	.announcements-view .ann-form-actions .btn-primary:hover {
		transform: translateY(-2px);
		box-shadow: 0 14px 30px rgba(2, 83, 204, .4);
	}

	.announcements-view .ann-form-actions .btn-primary[disabled] {
		opacity: .5;
		transform: none;
		box-shadow: none;
	}

	.announcements-view .ann-form-actions .btn-default {
		border-radius: 12px;
		font-weight: 600;
		padding: 12px 24px;
		border: 1.5px solid var(--ann-border);
		background: #fff;
	}

	/* ============ Mobile phone preview ============ */
	.announcements-view .mobile-preview {
		width: 260px;
		height: 460px;
		border: 8px solid var(--ann-ink);
		border-radius: 30px;
		margin: 0 auto;
		background: #fff;
		overflow-y: auto;
		font-family: 'Inter', sans-serif;
		box-shadow: var(--ann-shadow-lg);
		position: relative;
	}

	.announcements-view .mobile-preview::before {
		content: '';
		position: absolute;
		top: 0;
		left: 50%;
		transform: translateX(-50%);
		width: 70px;
		height: 18px;
		background: var(--ann-ink);
		border-radius: 0 0 12px 12px;
		z-index: 2;
	}

	.announcements-view .mobile-preview .preview-header {
		background: -webkit-linear-gradient(left, var(--ann-cyan) 0%, var(--ann-primary) 100%);
		background: linear-gradient(90deg, var(--ann-cyan), var(--ann-primary));
		color: white;
		padding: 26px 16px 14px;
		text-align: center;
		font-size: 15px;
		font-weight: 700;
		font-family: 'Plus Jakarta Sans', sans-serif;
	}

	.announcements-view .mobile-preview .preview-content {
		padding: 16px;
	}

	.announcements-view .mobile-preview .preview-title {
		font-size: 16px;
		font-weight: 700;
		margin-bottom: 6px;
		color: var(--ann-ink);
		font-family: 'Plus Jakarta Sans', sans-serif;
	}

	.announcements-view .mobile-preview .preview-message {
		font-size: 13.5px;
		line-height: 1.6;
		color: var(--ann-muted);
		white-space: pre-wrap;
		word-break: break-word;
	}

	.announcements-view .mobile-preview hr {
		margin: 10px 0;
	}

	/* ============ Animations ============ */
	@keyframes annRing {
		0% {
			transform: scale(.85);
			opacity: .7;
		}

		100% {
			transform: scale(1.9);
			opacity: 0;
		}
	}

	@keyframes annGradientShift {
		0% {
			background-position: 0% 50%;
		}

		50% {
			background-position: 100% 50%;
		}

		100% {
			background-position: 0% 50%;
		}
	}

	@keyframes annUrgentPulse {

		0%,
		100% {
			box-shadow: 0 0 0 0 rgba(244, 63, 94, .35);
		}

		50% {
			box-shadow: 0 0 0 5px rgba(244, 63, 94, 0);
		}
	}

	@keyframes annBounceIn {
		0% {
			transform: scale(.6);
			opacity: 0;
		}

		60% {
			transform: scale(1.08);
			opacity: 1;
		}

		100% {
			transform: scale(1);
		}
	}

	@keyframes annFadeInUp {
		from {
			opacity: 0;
			transform: translateY(10px);
		}

		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.announcements-view * {
			animation: none !important;
			transition: none !important;
		}
	}

	/* ============ Responsive: card-list table on small screens ============ */
	@media (max-width: 768px) {
		.ann-header {
			margin-bottom: 18px;
		}

		.ann-btn-primary {
			width: 100%;
			justify-content: center;
		}

		.announcements-view table.custom-table thead {
			display: none;
		}

		.announcements-view table.custom-table,
		.announcements-view table.custom-table tbody,
		.announcements-view table.custom-table tr,
		.announcements-view table.custom-table td {
			display: block;
			width: 100%;
		}

		.announcements-view table.custom-table tr {
			border: 1px solid var(--ann-border);
			border-radius: 14px;
			margin: 14px;
			padding: 6px 16px;
			box-shadow: 0 2px 10px rgba(30, 27, 46, .05);
		}

		.announcements-view table.custom-table td {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			padding: 11px 0;
			border-top: none;
			border-bottom: 1px dashed var(--ann-border);
			text-align: right;
			font-size: 13.5px;
		}

		.announcements-view table.custom-table td:last-child {
			border-bottom: none;
			justify-content: flex-end;
		}

		.announcements-view table.custom-table td::before {
			content: attr(data-label);
			font-weight: 700;
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: .05em;
			color: var(--ann-muted);
			text-align: left;
		}

		.announcements-view table.custom-table td[data-label="Title"] {
			flex-direction: column;
			align-items: flex-start;
			padding-top: 16px;
		}

		.announcements-view table.custom-table td[data-label="Title"]::before {
			margin-bottom: 6px;
		}

		.ann-title-text {
			max-width: 100%;
			white-space: normal;
		}

		.ann-row-actions,
		.ann-confirm-strip {
			width: 100%;
			justify-content: flex-end;
		}

		.ann-form-card-body {
			padding: 18px;
		}

		.announcements-view .ann-form-actions {
			flex-direction: column-reverse;
		}

		.announcements-view .ann-form-actions .btn {
			width: 100%;
		}

		.announcements-view .mobile-preview {
			margin-top: 10px;
		}
	}
</style>

<div ng-app="myApp" ng-controller="announcementCtrl" class="announcements-view">
	<div class="page-wrapper" >
		<div class="content container-fluid" style="padding-left: 50px;">

			<div class="ann-header">
				<div class="ann-header-left">
					<div class="ann-header-icon">
						<span class="ann-ring"></span>
						<i class="fa fa-bullhorn"></i>
					</div>
					<div>
						<h4 class="ann-page-title">Announcements</h4>
						<p class="ann-page-subtitle" ng-show="view=='list'">Broadcast updates to outlets, departments, and teams</p>
						<p class="ann-page-subtitle" ng-show="view=='add'">Create a new announcement</p>
						<p class="ann-page-subtitle" ng-show="view=='edit'">Editing: {{editModel.title}}</p>
					</div>
				</div>

				<a href="#" class="ann-btn-primary" ng-show="view=='list'" ng-click="goToAdd()">
					<span class="ann-btn-icon"><i class="fa fa-plus"></i></span> Add Announcement
				</a>
				<a href="#" class="ann-btn-ghost" ng-show="view!='list'" ng-click="goToList()">
					<i class="fa fa-arrow-left"></i> Back to list
				</a>
			</div>

			<!-- ============ "Viewed by" modal ============ -->
			<!-- ng-if keeps this OUT of the DOM entirely until viewViewers() sets viewersModal.open = true.
			     ng-cloak hides it during the brief moment before Angular finishes compiling the page,
			     so it can never flash open on page load — it only appears when the eye-icon button is clicked. -->
			<div class="ann-modal-backdrop" ng-if="viewersModal.open" ng-cloak ng-click="closeViewersModal($event)">
				<div class="ann-modal" ng-click="$event.stopPropagation()">
					<div class="ann-modal-header">
						<div>
							<h4>Viewed by</h4>
							<p>{{viewersModal.title}}</p>
						</div>
						<button type="button" class="ann-modal-close" ng-click="closeViewersModal()"><i class="fa fa-times"></i></button>
					</div>
					<div class="ann-modal-body">
						<div class="ann-modal-loading" ng-show="viewersModal.loading">
							<i class="fa fa-spinner fa-spin"></i>
							<span>Loading viewers&hellip;</span>
						</div>
						<div class="ann-modal-empty" ng-show="!viewersModal.loading && viewersModal.list.length === 0">
							<i class="fa fa-eye-slash"></i>
							<span>No one has viewed this announcement yet.</span>
						</div>
						<div ng-show="!viewersModal.loading" ng-repeat="viewer in viewersModal.list">
							<div class="ann-viewer-row">
								<div class="ann-viewer-avatar">{{viewer.first_name.charAt(0)}}</div>
								<div>
									<p class="ann-viewer-name">{{viewer.first_name}} <span ng-show="viewer.special_id">({{viewer.special_id}})</span></p>
									<p class="ann-viewer-meta">{{viewer.branch_name || 'No outlet'}}</p>
								</div>
								<div class="ann-viewer-time">{{viewer.viewed_at | date:'d MMM y, h:mm a'}}</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- ============ LIST VIEW ============ -->
			<div ng-show="view=='list'">
				<?php
				$ann_total = count($announcements);
				$ann_active = 0;
				$ann_draft = 0;
				$ann_urgent = 0;
				foreach ($announcements as $ann_stat) {
					if ($ann_stat->display_status == 'Active') $ann_active++;
					if ($ann_stat->display_status == 'Draft') $ann_draft++;
					if ($ann_stat->priority == 'urgent') $ann_urgent++;
				}
				?>
				<div class="ann-stats">
					<div class="ann-stat-card">
						<div class="ann-stat-icon ann-stat-total"><i class="fa fa-bullhorn"></i></div>
						<div>
							<div class="ann-stat-value"><?php echo $ann_total; ?></div>
							<div class="ann-stat-label">Total</div>
						</div>
					</div>
					<div class="ann-stat-card">
						<div class="ann-stat-icon ann-stat-active"><i class="fa fa-check-circle"></i></div>
						<div>
							<div class="ann-stat-value"><?php echo $ann_active; ?></div>
							<div class="ann-stat-label">Active</div>
						</div>
					</div>
					<div class="ann-stat-card">
						<div class="ann-stat-icon ann-stat-draft"><i class="fa fa-file-text-o"></i></div>
						<div>
							<div class="ann-stat-value"><?php echo $ann_draft; ?></div>
							<div class="ann-stat-label">Draft</div>
						</div>
					</div>
					<div class="ann-stat-card">
						<div class="ann-stat-icon ann-stat-urgent"><i class="fa fa-exclamation-circle"></i></div>
						<div>
							<div class="ann-stat-value"><?php echo $ann_urgent; ?></div>
							<div class="ann-stat-label">Urgent</div>
						</div>
					</div>
				</div>
				<?php if (!empty($announcements)) : ?>
					<div class="ann-table-card">
						<div class="table-responsive" style="padding: 10px;">
							<table id="datatable_announcements" class="table table-striped custom-table datatable">
								<thead>
									<tr>
										<th>Title</th>
										<th>Priority</th>
										<th>Start Date</th>
										<th>End Date</th>
										<th>Status</th>
										<th>Push Notification</th>
										<th>Views</th>
										<th class="text-right">Action</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($announcements as $ann) : ?>
										<tr>
											<td data-label="Title">
												<p class="ann-title-text" title="<?php echo htmlspecialchars($ann->title, ENT_QUOTES); ?>"><?php echo $ann->title; ?></p>
											</td>
											<td data-label="Priority">
												<span class="ann-badge <?php
																		echo $ann->priority == 'urgent' ? 'ann-badge-urgent' : ($ann->priority == 'important' ? 'ann-badge-important' : 'ann-badge-normal');
																		?>">
													<span class="ann-badge-dot"></span>
													<?php echo ucfirst($ann->priority); ?>
												</span>
											</td>
											<td data-label="Start Date"><?php echo date('d M Y', strtotime($ann->start_date)); ?></td>
											<td data-label="End Date"><?php echo date('d M Y', strtotime($ann->end_date)); ?></td>
											<td data-label="Status">
												<span class="ann-badge <?php
																		echo $ann->display_status == 'Active' ? 'ann-badge-active' : ($ann->display_status == 'Closed' ? 'ann-badge-closed' : 'ann-badge-draft');
																		?>">
													<span class="ann-badge-dot"></span>
													<?php echo ucfirst($ann->display_status); ?>
												</span>
											</td>
											<td data-label="Push Notification">
												<?php if ($ann->push_notification): ?>
													<span class="ann-badge ann-badge-push">Enabled</span>
													<?php if ($ann->is_push_notification_sent): ?>
														<span class="ann-badge ann-badge-sent"><i class="fa fa-check"></i> Sent</span>
													<?php endif; ?>
												<?php else: ?>
													<span class="text-muted">&mdash;</span>
												<?php endif; ?>
											</td>
											<td data-label="Views">
												<button type="button" class="ann-views ann-views-btn" ng-click="viewViewers(<?php echo $ann->id; ?>, '<?php echo htmlspecialchars($ann->title, ENT_QUOTES); ?>')">
													<i class="fa fa-eye"></i> <?php echo $ann->view_count; ?>
												</button>
											</td>
											<td data-label="Action" class="text-right">
												<!-- Normal state: direct Edit / Delete buttons, no dropdown -->
												<div class="ann-row-actions" ng-hide="confirmDeleteId === <?php echo $ann->id; ?>">
													<button type="button" class="ann-icon-btn ann-edit-btn" ng-click="goToEdit(<?php echo $ann->id; ?>)">
														<i class="fa fa-pencil"></i> Edit
													</button>
													<button type="button" class="ann-icon-btn ann-delete-btn" ng-click="askDelete(<?php echo $ann->id; ?>, '<?php echo htmlspecialchars($ann->title, ENT_QUOTES); ?>')">
														<i class="fa fa-trash-o"></i> Delete
													</button>
												</div>
												<!-- Inline confirm state: replaces the buttons above, right in the row -->
												<div class="ann-confirm-strip" ng-show="confirmDeleteId === <?php echo $ann->id; ?>">
													<span>Delete this?</span>
													<button type="button" class="ann-confirm-yes" ng-click="confirmDelete()">Yes</button>
													<button type="button" class="ann-confirm-no" ng-click="cancelDelete()">Cancel</button>
												</div>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				<?php else : ?>
					<div class="ann-table-card">
						<div class="ann-empty-state">
							<div class="ann-empty-icon"><i class="fa fa-inbox"></i></div>
							<h5>No announcements yet</h5>
							<p>Create your first announcement to broadcast it to your team.</p>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- ============ ADD VIEW (separate code, same logic as Edit) ============ -->
			<div ng-show="view=='add'">
				<div class="ann-form-card">
					<div class="ann-form-card-header">
						<h4>Add Announcement</h4>
					</div>
					<div class="ann-form-card-body">
						<form class="m-b-30" name="add_form" id="add_form" ng-submit="save(add_form.$valid)">
							<div class="row">
								<div class="col-md-7">
									<h3>Announcement Details</h3>
									<div class="form-group">
										<label>Title <span class="text-danger">*</span></label>
										<input class="form-control" type="text" ng-model="addModel.title" required>
									</div>

									<div class="form-group">
										<label>Message <span class="text-danger">*</span></label>
										<textarea class="form-control" rows="10" ng-model="addModel.message" required></textarea>
									</div>

									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Start Date <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text" id="add_start_date" required></div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>End Date <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text" id="add_end_date" required></div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Priority <span class="text-danger">*</span></label>
												<select class="select" ng-model="addModel.priority" required>
													<option value="normal">Normal</option>
													<option value="important">Important</option>
													<option value="urgent">Urgent</option>
												</select>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Status <span class="text-danger">*</span></label>
												<select class="select" id="add_status" ng-model="addModel.status" required>
													<option value="active">Active</option>
													<option value="draft">Draft</option>
												</select>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Send Push Notification</label>
												<div class="checkbox">
													<label><input type="checkbox" ng-model="addModel.push_notification"></label>
												</div>
											</div>
										</div>
									</div>

									<hr>
									<h3>Target Audience</h3>
									<div class="form-group">
										<div class="checkbox">
											<label><input type="checkbox" ng-model="addModel.all_staff"> <b>All Staff</b> (Overrides all filters below)</label>
										</div>
									</div>

									<div ng-hide="addModel.all_staff">
										<div class="form-group">
											<label>Outlets</label>
											<select class="select2-multi" id="add_outlets" multiple="multiple" style="width: 100%;">
												<?php foreach ($branches as $br) : ?>
													<option value="<?php echo $br->id; ?>"><?php echo $br->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Departments</label>
											<select class="select2-multi" id="add_departments" multiple="multiple" style="width: 100%;">
												<?php foreach ($departments as $dep) : ?>
													<option value="<?php echo $dep->id; ?>"><?php echo $dep->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Positions</label>
											<select class="select2-multi" id="add_positions" multiple="multiple" style="width: 100%;">
												<?php foreach ($positions as $pos) : ?>
													<option value="<?php echo $pos->id; ?>"><?php echo $pos->title; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Sections</label>
											<select class="select2-multi" id="add_sections" multiple="multiple" style="width: 100%;">
												<?php foreach ($sections as $sec) : ?>
													<option value="<?php echo $sec->id; ?>"><?php echo $sec->title; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Individual Employees</label>
											<select class="select2-ajax-employees" id="add_employees" multiple="multiple" style="width: 100%;"></select>
										</div>
									</div>
								</div>
								<div class="col-md-5">
									<h3>Mobile Preview</h3>
									<div class="mobile-preview">
										<div class="preview-header">Announcements</div>
										<div class="preview-content">
											<div class="preview-title">{{addModel.title || 'Your Title Here'}}</div>
											<small class="text-muted">{{addModel.start_date || 'Start Date'}}</small>
											<hr>
											<div class="preview-message">
												{{addModel.message || 'Your message content will appear here... \n\nNew lines will be respected.'}}
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="ann-form-actions">
								<button class="btn btn-primary" type="submit" ng-disabled="add_form.$invalid">Create Announcement</button>
								<a href="#" class="btn btn-default" ng-click="goToList()">Cancel</a>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- ============ EDIT VIEW (separate code, same logic as Add) ============ -->
			<div ng-show="view=='edit'">
				<div class="ann-form-card">
					<div class="ann-form-card-header">
						<h4>Edit Announcement</h4>
					</div>
					<div class="ann-form-card-body">
						<form class="m-b-30" name="edit_form" id="edit_form" ng-submit="update(edit_form.$valid)">
							<div class="row">
								<div class="col-md-7">
									<h3>Announcement Details</h3>
									<div class="form-group">
										<label>Title <span class="text-danger">*</span></label>
										<input class="form-control" type="text" ng-model="editModel.title" required>
									</div>

									<div class="form-group">
										<label>Message <span class="text-danger">*</span></label>
										<textarea class="form-control" rows="10" ng-model="editModel.message" required></textarea>
									</div>

									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Start Date <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text" id="edit_start_date" required></div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>End Date <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text" id="edit_end_date" required></div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Priority <span class="text-danger">*</span></label>
												<select class="select" id="edit_priority" ng-model="editModel.priority" required>
													<option value="normal">Normal</option>
													<option value="important">Important</option>
													<option value="urgent">Urgent</option>
												</select>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Status <span class="text-danger">*</span></label>
												<select class="select" id="edit_status" ng-model="editModel.status" required>
													<option value="active">Active</option>
													<option value="draft">Draft</option>
													<option value="closed">Closed</option>
												</select>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Send Push Notification</label>
												<div class="checkbox">
													<label><input type="checkbox" ng-model="editModel.push_notification"></label>
												</div>
											</div>
										</div>
									</div>

									<hr>
									<h3>Target Audience</h3>
									<div class="form-group">
										<div class="checkbox">
											<label><input type="checkbox" ng-model="editModel.all_staff"> <b>All Staff</b> (Overrides all filters below)</label>
										</div>
									</div>

									<div ng-hide="editModel.all_staff">
										<div class="form-group">
											<label>Outlets</label>
											<select class="select2-multi" id="edit_outlets" multiple="multiple" style="width: 100%;">
												<?php foreach ($branches as $br) : ?>
													<option value="<?php echo $br->id; ?>"><?php echo $br->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Departments</label>
											<select class="select2-multi" id="edit_departments" multiple="multiple" style="width: 100%;">
												<?php foreach ($departments as $dep) : ?>
													<option value="<?php echo $dep->id; ?>"><?php echo $dep->name; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Positions</label>
											<select class="select2-multi" id="edit_positions" multiple="multiple" style="width: 100%;">
												<?php foreach ($positions as $pos) : ?>
													<option value="<?php echo $pos->id; ?>"><?php echo $pos->title; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Sections</label>
											<select class="select2-multi" id="edit_sections" multiple="multiple" style="width: 100%;">
												<?php foreach ($sections as $sec) : ?>
													<option value="<?php echo $sec->id; ?>"><?php echo $sec->title; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label>Individual Employees</label>
											<select class="select2-ajax-employees" id="edit_employees" multiple="multiple" style="width: 100%;"></select>
										</div>
									</div>
								</div>
								<div class="col-md-5">
									<h3>Mobile Preview</h3>
									<div class="mobile-preview">
										<div class="preview-header">Announcements</div>
										<div class="preview-content">
											<div class="preview-title">{{editModel.title || 'Your Title Here'}}</div>
											<small class="text-muted">{{editModel.start_date | date:'medium'}}</small>
											<hr>
											<div class="preview-message">{{editModel.message}}</div>
										</div>
									</div>
								</div>
							</div>

							<div class="ann-form-actions">
								<button class="btn btn-primary" type="submit" ng-disabled="edit_form.$invalid">Save Changes</button>
								<a href="#" class="btn btn-default" ng-click="goToList()">Cancel</a>
							</div>
						</form>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<script type="text/javascript">
	var config = {
		headers: {
			'Content-Type': 'application/json;charset=utf-8;'
		}
	};

	// Initialize libraries
	$(document).ready(function() {
		// Init Datatable
		$('#datatable_announcements').DataTable({
			"order": [
				[2, "desc"]
			] // Default sort by Start Date
		});

		// Init standard multi-select
		$('.select2-multi').select2();

		// Init employee search dropdown
		$('.select2-ajax-employees').select2({
			ajax: {
				url: "<?php echo base_url('announcements/searchEmployees'); ?>",
				dataType: 'json',
				delay: 250,
				data: function(params) {
					return {
						term: params.term // search term
					};
				},
				processResults: function(data) {
					return data;
				},
				cache: true
			},
			minimumInputLength: 2,
			placeholder: 'Search by name or ID'
		});

	});

	var app = angular.module('myApp', []);
	app.controller('announcementCtrl', function($scope, $http) {

		// Which "page" is showing: 'list' | 'add' | 'edit'
		$scope.view = 'list';

		// Model for Add Form
		$scope.addModel = {
			title: '',
			message: '',
			start_date: '',
			end_date: '',
			priority: 'normal',
			status: 'active',
			push_notification: false,
			all_staff: false,
			outlets: [],
			departments: [],
			positions: [],
			sections: [],
			employees: []
		};

		// Model for Edit Form
		$scope.editModel = {};
		$scope.deleteId = 0;
		$scope.deleteTitle = '';
		$scope.confirmDeleteId = null; // row currently showing the inline delete confirmation

		// State for the "Viewed by" modal — starts closed; only viewViewers() opens it
		$scope.viewersModal = {
			open: false,
			loading: false,
			title: '',
			list: []
		};

		// Reset + open the Add "page"
		$scope.goToAdd = function() {
			$scope.addModel = {
				title: '',
				message: '',
				start_date: '',
				end_date: '',
				priority: 'normal',
				status: 'active',
				push_notification: false,
				all_staff: false,
				outlets: [],
				departments: [],
				positions: [],
				sections: [],
				employees: []
			};
			$scope.view = 'add';

			// Reset jQuery controls once Angular has shown the add view
			setTimeout(function() {
				$('#add_start_date').val('');
				$('#add_end_date').val('');
				$('#add_outlets').val(null).trigger('change');
				$('#add_status').val('active').trigger('change');
				$('#add_departments').val(null).trigger('change');
				$('#add_positions').val(null).trigger('change');
				$('#add_sections').val(null).trigger('change');
				$('#add_employees').val(null).trigger('change');
				$('#add_form .select').val('normal').trigger('change');
			}, 0);
		};

		// Back to the list "page"
		$scope.goToList = function() {
			$scope.view = 'list';
		};

		// Fetch data and open the Edit "page"
		$scope.goToEdit = function(id) {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>announcements/getSingleAnnouncement', {
				id: id
			}, config).then(function(response) {
				if (response.data.success) {
					$scope.editModel = response.data.announcement;

					// Convert types for checkboxes
					$scope.editModel.push_notification = $scope.editModel.push_notification == '1';
					$scope.editModel.all_staff = $scope.editModel.all_staff == '1';

					$scope.view = 'edit';

					// Date Fix
					if ($scope.editModel.start_date) {
						$('#edit_start_date').val(moment($scope.editModel.start_date).format('DD/MM/YYYY'));
					} else {
						$('#edit_start_date').val('');
					}

					if ($scope.editModel.end_date) {
						$('#edit_end_date').val(moment($scope.editModel.end_date).format('DD/MM/YYYY'));
					} else {
						$('#edit_end_date').val('');
					}

					// Defer jQuery updates to the next digest cycle so the edit
					// view is visible in the DOM before select2/etc. touch it.
					setTimeout(function() {
						$('#edit_priority').val($scope.editModel.priority).trigger('change');
						$('#edit_status').val($scope.editModel.status).trigger('change');

						$('#edit_outlets').val(response.data.outlets).trigger('change');
						$('#edit_departments').val(response.data.departments).trigger('change');
						$('#edit_positions').val(response.data.positions).trigger('change');
						$('#edit_sections').val(response.data.sections).trigger('change');

						var $employeeSelect = $('#edit_employees');
						$employeeSelect.empty();
						if (response.data.employees.length > 0) {
							response.data.employees.forEach(function(emp) {
								var text = emp.first_name + ' (' + emp.special_id + ')';
								var option = new Option(text, emp.id, true, true);
								$employeeSelect.append(option);
							});
						}
						$employeeSelect.trigger('change');
					}, 0);

				} else {
					showNotification("Error", "Could not load announcement data.", "error");
				}
				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
				$('body').LoadingOverlay("hide");
			});
		};

		// Show the inline "Delete this?" confirmation inside the row
		$scope.askDelete = function(id, title) {
			$scope.deleteId = id;
			$scope.deleteTitle = title;
			$scope.confirmDeleteId = id;
		};

		// Dismiss the inline confirmation without deleting
		$scope.cancelDelete = function() {
			$scope.confirmDeleteId = null;
			$scope.deleteId = 0;
			$scope.deleteTitle = '';
		};

		// Save new announcement
		$scope.save = function(valid) {
			if (!valid) {
				showNotification("Error", "Please fill all required fields.", "error");
				return;
			}
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});

			$scope.addModel.start_date = $('#add_start_date').val();
			$scope.addModel.end_date = $('#add_end_date').val();
			$scope.addModel.outlets = $('#add_outlets').val();
			$scope.addModel.departments = $('#add_departments').val();
			$scope.addModel.positions = $('#add_positions').val();
			$scope.addModel.sections = $('#add_sections').val();
			$scope.addModel.employees = $('#add_employees').val();

			$http.post('<?php echo base_url(); ?>announcements/save', $scope.addModel, config).then(function(response) {
				if (response.data.success) {
					showNotification("Success", response.data.msg, "success");
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					showNotification("Error", response.data.msg, "error");
				}
				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
				$('body').LoadingOverlay("hide");
			});
		};

		// Update existing announcement
		$scope.update = function(valid) {
			if (!valid) {
				showNotification("Error", "Please fill all required fields.", "error");
				return;
			}
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});

			$scope.editModel.start_date = $('#edit_start_date').val();
			$scope.editModel.end_date = $('#edit_end_date').val();
			$scope.editModel.outlets = $('#edit_outlets').val();
			$scope.editModel.departments = $('#edit_departments').val();
			$scope.editModel.positions = $('#edit_positions').val();
			$scope.editModel.sections = $('#edit_sections').val();
			$scope.editModel.employees = $('#edit_employees').val();

			$http.post('<?php echo base_url(); ?>announcements/update', $scope.editModel, config).then(function(response) {
				if (response.data.success) {
					showNotification("Success", response.data.msg, "success");
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					showNotification("Error", response.data.msg, "error");
				}
				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
				$('body').LoadingOverlay("hide");
			});
		};

		// Delete announcement (triggered from the inline "Yes, delete" button)
		$scope.confirmDelete = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>announcements/delete', {
				id: $scope.deleteId
			}, config).then(function(response) {
				if (response.data.success) {
					showNotification("Success", "Announcement deleted.", "success");
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					showNotification("Error", "Could not delete announcement.", "error");
				}
				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
				$('body').LoadingOverlay("hide");
			});
		};

		// Open the "Viewed by" modal and fetch the list of employees who viewed this announcement.
		// The modal only exists in the DOM (ng-if) once this sets viewersModal.open = true —
		// it is never shown on page load, only on click.
		$scope.viewViewers = function(id, title) {
			$scope.viewersModal.open = true;
			$scope.viewersModal.loading = true;
			$scope.viewersModal.title = title;
			$scope.viewersModal.list = [];

			$http.post('<?php echo base_url(); ?>announcements/getAnnouncementViewers', {
				id: id
			}, config).then(function(response) {
				if (response.data.success) {
					$scope.viewersModal.list = response.data.viewers || [];
				} else {
					showNotification("Error", response.data.msg || "Could not load viewers.", "error");
					$scope.viewersModal.open = false;
				}
				$scope.viewersModal.loading = false;
			}, function(error) {
				console.log(error.data);
				showNotification("Error", "Something went wrong loading viewers.", "error");
				$scope.viewersModal.loading = false;
				$scope.viewersModal.open = false;
			});
		};

		// Close the modal — clicking the backdrop passes an $event, the × button doesn't.
		// The inner ann-modal panel stops propagation, so $event here only ever fires for
		// an actual backdrop click.
		$scope.closeViewersModal = function($event) {
			if ($event && $event.target !== $event.currentTarget) return;
			$scope.viewersModal.open = false;
		};

	});
</script>