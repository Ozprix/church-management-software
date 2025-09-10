<template>
  <div 
    class="search-result-item"
    :class="{ 'highlighted': highlighted }"
    @click="navigateToResult"
  >
    <div class="result-icon" :class="result.type">
      <i :class="getIconForType(result.type)"></i>
    </div>
    
    <div class="result-content">
      <div class="result-title">{{ getResultTitle() }}</div>
      <div class="result-subtitle">{{ getResultSubtitle() }}</div>
      
      <div class="result-details" v-if="showDetails">
        <div class="result-detail" v-if="result.description">
          <span class="detail-label">{{ $t('common.description') }}:</span>
          <span class="detail-value">{{ truncateText(result.description, 100) }}</span>
        </div>
        
        <div class="result-detail" v-if="result.category">
          <span class="detail-label">{{ $t('common.category') }}:</span>
          <span class="detail-value">{{ getCategoryLabel(result.category) }}</span>
        </div>
        
        <div class="result-detail" v-if="result.status">
          <span class="detail-label">{{ $t('common.status') }}:</span>
          <span class="detail-value status-badge" :class="result.status">
            {{ getStatusLabel(result.status) }}
          </span>
        </div>
      </div>
    </div>
    
    <div class="result-meta">
      <div class="result-type">{{ getTypeLabel(result.type) }}</div>
      <div class="result-date" v-if="result.date">{{ formatDate(result.date) }}</div>
      <button class="toggle-details-button" @click.stop="toggleDetails">
        <i :class="showDetails ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from '../../services/i18nService';
import { SEARCH_TYPES } from '../../services/searchService';

// Props
const props = defineProps({
  result: {
    type: Object,
    required: true
  },
  highlighted: {
    type: Boolean,
    default: false
  }
});

// Get router and i18n service
const router = useRouter();
const i18n = useI18n();

// Component state
const showDetails = ref(false);

// Methods
const getIconForType = (type) => {
  switch (type) {
    case 'member':
      return 'fas fa-user';
    case 'donation':
      return 'fas fa-hand-holding-usd';
    case 'event':
      return 'fas fa-calendar-alt';
    case 'attendance':
      return 'fas fa-clipboard-list';
    case 'group':
      return 'fas fa-users';
    case 'sermon':
      return 'fas fa-bible';
    case 'resource':
      return 'fas fa-file-alt';
    default:
      return 'fas fa-search';
  }
};

const getTypeLabel = (type) => {
  switch (type) {
    case SEARCH_TYPES.ALL:
      return i18n.t('search.all');
    case SEARCH_TYPES.MEMBERS:
    case 'member':
      return i18n.t('search.member');
    case SEARCH_TYPES.DONATIONS:
    case 'donation':
      return i18n.t('search.donation');
    case SEARCH_TYPES.EVENTS:
    case 'event':
      return i18n.t('search.event');
    case SEARCH_TYPES.ATTENDANCE:
    case 'attendance':
      return i18n.t('search.attendance');
    case 'group':
      return i18n.t('search.group');
    case 'sermon':
      return i18n.t('search.sermon');
    case 'resource':
      return i18n.t('search.resource');
    default:
      return type;
  }
};

const getResultTitle = () => {
  const result = props.result;
  
  switch (result.type) {
    case 'member':
      return `${result.first_name || ''} ${result.last_name || ''}`.trim() || 'Unnamed Member';
    case 'donation':
      const amount = result.amount ? `${result.amount} ${result.currency || 'USD'}` : '';
      const donor = result.donor_name || 'Anonymous';
      return amount ? `${amount} - ${donor}` : donor;
    case 'event':
      return result.name || 'Unnamed Event';
    case 'attendance':
      return result.event_name || 'Unnamed Event';
    case 'group':
      return result.name || 'Unnamed Group';
    case 'sermon':
      return result.title || 'Unnamed Sermon';
    case 'resource':
      return result.title || result.name || 'Unnamed Resource';
    default:
      return result.title || result.name || 'Unnamed Item';
  }
};

const getResultSubtitle = () => {
  const result = props.result;
  
  switch (result.type) {
    case 'member':
      return result.email || result.phone || '';
    case 'donation':
      return result.purpose || result.notes || '';
    case 'event':
      return result.location || '';
    case 'attendance':
      return `${result.count || 0} ${i18n.t('attendance.attendees')}`;
    case 'group':
      return `${result.member_count || 0} ${i18n.t('groups.members')}`;
    case 'sermon':
      return result.speaker || '';
    case 'resource':
      return result.description || '';
    default:
      return result.description || '';
  }
};

const getCategoryLabel = (category) => {
  if (!category) return '';
  
  const result = props.result;
  
  switch (result.type) {
    case 'member':
      return i18n.t(`members.categories.${category}`) || category;
    case 'donation':
      return i18n.t(`donations.categories.${category}`) || category;
    case 'event':
      return i18n.t(`events.categories.${category}`) || category;
    case 'attendance':
      return i18n.t(`attendance.categories.${category}`) || category;
    default:
      return category;
  }
};

const getStatusLabel = (status) => {
  if (!status) return '';
  
  const result = props.result;
  
  switch (result.type) {
    case 'member':
      return i18n.t(`members.statuses.${status}`) || status;
    case 'donation':
      return i18n.t(`donations.statuses.${status}`) || status;
    case 'event':
      return i18n.t(`events.statuses.${status}`) || status;
    default:
      return status;
  }
};

const formatDate = (date) => {
  if (!date) return '';
  
  try {
    return i18n.formatDateTime(date, 'PPP');
  } catch (error) {
    return date;
  }
};

const truncateText = (text, maxLength) => {
  if (!text) return '';
  if (text.length <= maxLength) return text;
  
  return text.substring(0, maxLength) + '...';
};

const toggleDetails = () => {
  showDetails.value = !showDetails.value;
};

const navigateToResult = () => {
  const result = props.result;
  
  switch (result.type) {
    case 'member':
      router.push({ name: 'member-details', params: { id: result.id } });
      break;
    case 'donation':
      router.push({ name: 'donation-details', params: { id: result.id } });
      break;
    case 'event':
      router.push({ name: 'event-details', params: { id: result.id } });
      break;
    case 'attendance':
      router.push({ name: 'attendance-details', params: { id: result.id } });
      break;
    case 'group':
      router.push({ name: 'group-details', params: { id: result.id } });
      break;
    case 'sermon':
      router.push({ name: 'sermon-details', params: { id: result.id } });
      break;
    case 'resource':
      router.push({ name: 'resource-details', params: { id: result.id } });
      break;
    default:
      // Do nothing for unknown types
      break;
  }
};
</script>

<style scoped>
.search-result-item {
  display: flex;
  padding: 1rem;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.search-result-item:hover,
.search-result-item.highlighted {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transform: translateY(-2px);
}

.result-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  background-color: #e5e7eb;
  border-radius: 0.5rem;
  margin-right: 1rem;
  color: #4b5563;
  flex-shrink: 0;
}

.result-icon.member {
  background-color: #dbeafe;
  color: #2563eb;
}

.result-icon.donation {
  background-color: #d1fae5;
  color: #059669;
}

.result-icon.event {
  background-color: #fef3c7;
  color: #d97706;
}

.result-icon.attendance {
  background-color: #e0e7ff;
  color: #4f46e5;
}

.result-icon.group {
  background-color: #fce7f3;
  color: #db2777;
}

.result-icon.sermon {
  background-color: #ede9fe;
  color: #7c3aed;
}

.result-icon.resource {
  background-color: #e0f2fe;
  color: #0284c7;
}

.result-content {
  flex: 1;
  min-width: 0;
}

.result-title {
  font-weight: 600;
  font-size: 1rem;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.result-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.result-details {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.result-detail {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.detail-label {
  font-weight: 500;
  color: #4b5563;
  margin-right: 0.5rem;
}

.detail-value {
  color: #6b7280;
}

.status-badge {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-badge.active,
.status-badge.completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.inactive,
.status-badge.cancelled {
  background-color: #fee2e2;
  color: #b91c1c;
}

.status-badge.pending,
.status-badge.upcoming {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.ongoing {
  background-color: #dbeafe;
  color: #1e40af;
}

.result-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-left: 1rem;
  min-width: 7rem;
}

.result-type {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  text-transform: capitalize;
  margin-bottom: 0.25rem;
}

.result-date {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-bottom: 0.5rem;
}

.toggle-details-button {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0.25rem;
  font-size: 0.875rem;
}

.toggle-details-button:hover {
  color: #3b82f6;
}

/* Dark mode support */
:global(.dark) .search-result-item {
  background-color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

:global(.dark) .search-result-item:hover,
:global(.dark) .search-result-item.highlighted {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
}

:global(.dark) .result-title {
  color: #f3f4f6;
}

:global(.dark) .result-subtitle {
  color: #9ca3af;
}

:global(.dark) .result-details {
  border-top-color: #4b5563;
}

:global(.dark) .detail-label {
  color: #d1d5db;
}

:global(.dark) .detail-value {
  color: #9ca3af;
}

:global(.dark) .status-badge.active,
:global(.dark) .status-badge.completed {
  background-color: rgba(6, 95, 70, 0.2);
  color: #34d399;
}

:global(.dark) .status-badge.inactive,
:global(.dark) .status-badge.cancelled {
  background-color: rgba(185, 28, 28, 0.2);
  color: #f87171;
}

:global(.dark) .status-badge.pending,
:global(.dark) .status-badge.upcoming {
  background-color: rgba(146, 64, 14, 0.2);
  color: #fbbf24;
}

:global(.dark) .status-badge.ongoing {
  background-color: rgba(30, 64, 175, 0.2);
  color: #60a5fa;
}

:global(.dark) .result-type {
  color: #9ca3af;
}

:global(.dark) .result-date {
  color: #6b7280;
}

:global(.dark) .toggle-details-button {
  color: #9ca3af;
}

:global(.dark) .toggle-details-button:hover {
  color: #60a5fa;
}
</style>
