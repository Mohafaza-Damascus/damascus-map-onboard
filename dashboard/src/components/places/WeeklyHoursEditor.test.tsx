// @vitest-environment jsdom
import { useState } from 'react'
import { cleanup, fireEvent, render, screen } from '@testing-library/react'
import { afterEach, expect, it } from 'vitest'
import WeeklyHoursEditor from './WeeklyHoursEditor'

afterEach(cleanup)
function Editor() {
  const [hours, setHours] = useState<Record<string, unknown>>({})
  return <><WeeklyHoursEditor value={hours} onChange={setHours} /><output data-testid="hours">{JSON.stringify(hours)}</output></>
}
it('updates hours directly, copies the schedule, and distinguishes closed from unspecified', () => {
  render(<Editor />)
  fireEvent.change(screen.getByLabelText('حالة السبت'), { target: { value: 'hours' } })
  fireEvent.change(screen.getByLabelText('وقت فتح السبت'), { target: { value: '08:00' } })
  fireEvent.click(screen.getByText('نسخ دوام السبت لكل الأيام'))
  expect((screen.getByLabelText('وقت فتح الجمعة') as HTMLInputElement).value).toBe('08:00')
  fireEvent.change(screen.getByLabelText('حالة الجمعة'), { target: { value: 'closed' } })
  expect(JSON.parse(screen.getByTestId('hours').textContent!).الجمعة).toBe('مغلق')
  fireEvent.change(screen.getByLabelText('حالة الجمعة'), { target: { value: '' } })
  expect(JSON.parse(screen.getByTestId('hours').textContent!).الجمعة).toBeUndefined()
})
it('validates missing and equal times and explains overnight hours', () => {
  render(<Editor />)
  fireEvent.change(screen.getByLabelText('حالة السبت'), { target: { value: 'hours' } })
  const end = screen.getByLabelText('وقت إغلاق السبت') as HTMLInputElement
  fireEvent.change(end, { target: { value: '09:00' } })
  expect(end.checkValidity()).toBe(false)
  fireEvent.change(end, { target: { value: '' } })
  expect(end.checkValidity()).toBe(false)
  fireEvent.change(end, { target: { value: '02:00' } })
  expect(end.checkValidity()).toBe(true)
  expect(screen.getByText('الإغلاق في اليوم التالي.')).toBeTruthy()
  fireEvent.click(screen.getByText('مفتوح 24 ساعة طوال الأسبوع'))
  expect(Object.values(JSON.parse(screen.getByTestId('hours').textContent!))).toEqual(Array(7).fill('24/7'))
})
