const days = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة']

export default function WeeklyHoursEditor({ value, onChange }: { value: Record<string, unknown>; onChange: (hours: Record<string, unknown>) => void }) {
  const setDay = (day: string, text: string) => {
    const next = { ...value }
    if (text) next[day] = text
    else delete next[day]
    onChange(next)
  }
  const saturday = typeof value[days[0]] === 'string' ? String(value[days[0]]) : ''
  return <section className="weekly-hours" aria-label="ساعات العمل الأسبوعية">
    <p>اختر حالة كل يوم، ثم أدخل وقت الفتح والإغلاق. تُحفظ الساعات عند حفظ المكان. يمكنك ترك الأيام غير المعروفة دون تحديد.</p>
    <div className="hours-shortcuts">
      <button type="button" disabled={!saturday} onClick={() => onChange({ ...value, ...Object.fromEntries(days.map(day => [day, saturday])) })}>نسخ دوام السبت لكل الأيام</button>
      <button type="button" onClick={() => onChange({ ...value, ...Object.fromEntries(days.map(day => [day, '24/7'])) })}>مفتوح 24 ساعة طوال الأسبوع</button>
    </div>
    {days.map(day => {
      const text = typeof value[day] === 'string' ? String(value[day]) : ''
      const times = text.match(/^(\d{2}:\d{2}|)[–-](\d{2}:\d{2}|)$/)
      const mode = !text ? '' : text === 'مغلق' ? 'closed' : text === '24/7' ? 'all-day' : times ? 'hours' : 'custom'
      return <div className="weekly-hours-day" key={day}>
        <label>{day}<select aria-label={`حالة ${day}`} value={mode} onChange={e => setDay(day, ({ hours: '09:00–17:00', closed: 'مغلق', 'all-day': '24/7' } as Record<string, string>)[e.target.value] || '')}>
          <option value="">غير محدد</option><option value="hours">ساعات محددة</option><option value="all-day">24 ساعة</option><option value="closed">مغلق</option>
          {mode === 'custom' && <option value="custom">{text}</option>}
        </select></label>
        {times && <div className="weekly-hours-times">
          <label>يفتح<input type="time" name={`hours_start_${day}`} aria-label={`وقت فتح ${day}`} required value={times[1]} onChange={e => setDay(day, `${e.target.value}–${times[2]}`)} /></label>
          <label>يغلق<input type="time" name={`hours_end_${day}`} aria-label={`وقت إغلاق ${day}`} required value={times[2]} ref={el => { el?.setCustomValidity(times[1] && times[1] === times[2] ? 'اختر وقتين مختلفين أو 24 ساعة.' : '') }} onChange={e => setDay(day, `${times[1]}–${e.target.value}`)} /></label>
          {times[1] === times[2] && <small role="alert" className="field-error">اختر وقتين مختلفين أو «24 ساعة».</small>}
          {times[1] && times[2] && times[2] < times[1] && <small>الإغلاق في اليوم التالي.</small>}
        </div>}
      </div>
    })}
    <small className="form-hint">«غير محدد» يعني أن الدوام غير معروف، و«مغلق» يعني يوم عطلة.</small>
  </section>
}
